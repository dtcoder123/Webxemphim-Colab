<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * FAIA — Film.sys Artificial Intelligence Assistant.
 *
 * Harness gồm 3 phần:
 *  1. Bộ phân loại ý định: softmax regression (unigram + bigram), trọng số nằm trong model.json.
 *  2. Tự học: câu được phân loại chắc chắn (>= LEARN_CONFIDENCE) đưa vào bộ đệm; đủ ROUND_SIZE
 *     câu thì chạy một vòng train SGD (ROUND_EPOCHS epoch, learning rate nhỏ) rồi gộp vào model.
 *  3. Trả lời dựa 100% vào database: phim không có trong kho thì báo "chưa có", không bao giờ bịa.
 */
class Faia
{
    const LEARN_CONFIDENCE = 0.85;
    const MIN_CONFIDENCE = 0.45;
    const ROUND_SIZE = 10;
    const ROUND_EPOCHS = 5;
    const ROUND_LR = 0.02;
    const MAX_MOVIES = 4;

    // Ý định "cá nhân / trang web": luôn ưu tiên hơn việc dò tên phim trong câu.
    const SITE_INTENTS = [
        'greet', 'thanks', 'bye', 'identity', 'capabilities', 'favorites', 'history',
        'login', 'register', 'forgot_password', 'logout', 'comment_help', 'watch_help',
        'list_genres', 'out_of_scope',
    ];

    // Thể loại phổ biến; dùng để báo "chưa có" khi kho phim không có thể loại đó.
    const KNOWN_GENRES = [
        'tinh cam' => 'Tình Cảm', 'lang man' => 'Lãng Mạn', 'phim hai' => 'Hài', 'hai huoc' => 'Hài Hước',
        'tam ly' => 'Tâm Lý', 'co trang' => 'Cổ Trang', 'vo thuat' => 'Võ Thuật', 'kiem hiep' => 'Kiếm Hiệp',
        'phieu luu' => 'Phiêu Lưu', 'chien tranh' => 'Chiến Tranh', 'the thao' => 'Thể Thao',
        'am nhac' => 'Âm Nhạc', 'tai lieu' => 'Tài Liệu', 'hinh su' => 'Hình Sự', 'trinh tham' => 'Trinh Thám',
        'gia dinh' => 'Gia Đình', 'than thoai' => 'Thần Thoại', 'gia tuong' => 'Giả Tưởng', 'hoc duong' => 'Học Đường',
        'zombie' => 'Zombie', 'sieu anh hung' => 'Siêu Anh Hùng', 'anime' => 'Anime',
        'kinh di' => 'Kinh Dị', 'hanh dong' => 'Hành Động', 'hoat hinh' => 'Hoạt Hình',
        'khoa hoc vien tuong' => 'Khoa Học Viễn Tưởng', 'vien tuong' => 'Viễn Tưởng',
    ];

    // Cách gọi khác của thể loại → khóa trong KNOWN_GENRES.
    const GENRE_ALIASES = [
        'khoa hoc' => 'khoa hoc vien tuong', 'sci fi' => 'khoa hoc vien tuong', 'scifi' => 'khoa hoc vien tuong',
        'phim ma' => 'kinh di', 'horror' => 'kinh di', 'rung ron' => 'kinh di', 'ma quai' => 'kinh di',
        'action' => 'hanh dong', 'cartoon' => 'hoat hinh', 'animation' => 'hoat hinh',
        'comedy' => 'phim hai', 'romance' => 'lang man', 'ngon tinh' => 'tinh cam',
    ];

    // Từ phổ thông không dùng làm "từ đặc trưng" của tên phim.
    const COMMON_WORDS = [
        'tuyet', 'phong', 'thich', 'nhieu', 'nhung', 'trong', 'nguoi', 'duoc', 'muon', 'nhat', 'hanh', 'dong',
        'kinh', 'tuong', 'cuoi', 'trang', 'thanh', 'thuong', 'nhanh', 'khong', 'chung', 'luong',
    ];

    // Từ đệm bỏ đi khi tách tên phim người dùng muốn tìm.
    const STOP = [
        'co', 'phim', 'bo', 'khong', 'ko', 'k', 'kg', 'hong', 'tim', 'kiem', 'tra', 'cho', 'minh', 'toi', 'tui',
        'em', 'anh', 'chi', 'ban', 'oi', 'faia', 'bot', 'xem', 'muon', 'nao', 'ten', 'la', 'duoc', 'voi', 'nhe',
        'nha', 'giup', 'hay', 'search', 'a', 'ha', 'the', 'gi', 'di', 'xin', 'hoi', 'web', 'website', 'trang',
        'nay', 'tren', 'trong', 'kho', 'o', 'day', 'van', 'con', 'chua', 've', 'cai', 'thong', 'tin', 'mot',
        'can', 'dang', 'ma', 'vay', 'u', 'um', 'oke', 'ok', 'ah', 'nhi', 'va', 'cua', 'of', 'the',
    ];

    private static ?array $model = null;

    public static function modelPath(): string
    {
        return database_path('faia/model.json');
    }

    /* ======================= MODEL / HỌC MÁY ======================= */

    public static function normalize(string $text): string
    {
        $text = Str::ascii(mb_strtolower($text));
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);

        // Teencode / viết tắt thường gặp.
        $slang = ['ko' => 'khong', 'k' => 'khong', 'kh' => 'khong', 'khg' => 'khong', 'kg' => 'khong', 'hok' => 'khong',
            'hong' => 'khong', 'dc' => 'duoc', 'j' => 'gi', 'mk' => 'minh', 'mik' => 'minh', 'bn' => 'ban', 'vs' => 'voi',
            'ns' => 'noi', 'pls' => 'giup', 'plz' => 'giup', 'film' => 'phim', 'movie' => 'phim'];

        return trim(implode(' ', array_map(fn ($t) => $slang[$t] ?? $t, explode(' ', $text))));
    }

    public static function features(string $text): array
    {
        $tokens = array_values(array_filter(explode(' ', self::normalize($text)), 'strlen'));
        $feats = $tokens;

        for ($i = 0; $i < count($tokens) - 1; $i++) {
            $feats[] = $tokens[$i] . '_' . $tokens[$i + 1];
        }

        return array_values(array_unique($feats));
    }

    public static function emptyModel(array $intents): array
    {
        return [
            'name' => 'FAIA',
            'epochs' => 0,
            'rounds' => 0,
            'intents' => array_values($intents),
            'b' => array_fill_keys($intents, 0.0),
            'w' => [],
            'buffer' => [],
        ];
    }

    /** Điểm softmax của mọi ý định cho một tập đặc trưng. */
    private static function probs(array $model, array $feats): array
    {
        $scores = $model['b'];

        foreach ($feats as $f) {
            foreach ($model['w'][$f] ?? [] as $intent => $weight) {
                $scores[$intent] += $weight;
            }
        }

        $max = max($scores);
        $sum = 0.0;

        foreach ($scores as $k => $s) {
            $scores[$k] = exp($s - $max);
            $sum += $scores[$k];
        }

        foreach ($scores as $k => $s) {
            $scores[$k] = $s / $sum;
        }

        return $scores;
    }

    /**
     * Train SGD trên các mẫu [intent, text]. Dùng chung cho lần train đầu và các vòng tự học.
     * $onEpoch(epoch, loss) để script train in tiến trình.
     */
    public static function train(array $model, array $samples, int $epochs, float $lr, ?callable $onEpoch = null): array
    {
        $data = [];

        foreach ($samples as [$intent, $text]) {
            if (in_array($intent, $model['intents'], true) && ($feats = self::features($text))) {
                $data[] = [$intent, $feats];
            }
        }

        for ($epoch = 1; $epoch <= $epochs; $epoch++) {
            shuffle($data);
            $rate = $lr / (1 + 0.02 * $epoch);
            $loss = 0.0;

            foreach ($data as [$intent, $feats]) {
                $p = self::probs($model, $feats);
                $loss -= log(max($p[$intent], 1e-12));

                foreach ($p as $k => $pk) {
                    $g = $rate * ($pk - ($k === $intent ? 1 : 0));

                    if (abs($g) < 1e-6) {
                        continue;
                    }

                    $model['b'][$k] -= $g;

                    foreach ($feats as $f) {
                        $model['w'][$f][$k] = ($model['w'][$f][$k] ?? 0.0) - $g;
                    }
                }
            }

            $model['epochs']++;

            if ($onEpoch) {
                $onEpoch($epoch, $loss / max(1, count($data)));
            }
        }

        return $model;
    }

    /** Làm tròn và bỏ trọng số ~0 cho model gọn. */
    public static function compact(array $model): array
    {
        foreach ($model['w'] as $f => $row) {
            foreach ($row as $k => $v) {
                if (abs($v) < 0.001) {
                    unset($row[$k]);
                } else {
                    $row[$k] = round($v, 4);
                }
            }

            if ($row) {
                $model['w'][$f] = $row;
            } else {
                unset($model['w'][$f]);
            }
        }

        foreach ($model['b'] as $k => $v) {
            $model['b'][$k] = round($v, 4);
        }

        return $model;
    }

    public static function model(): ?array
    {
        if (self::$model === null && is_file(self::modelPath())) {
            self::$model = json_decode(file_get_contents(self::modelPath()), true);
        }

        return self::$model;
    }

    public static function save(array $model): void
    {
        @mkdir(dirname(self::modelPath()), 0775, true);
        file_put_contents(self::modelPath(), json_encode($model, JSON_UNESCAPED_UNICODE), LOCK_EX);
        self::$model = $model;
    }

    /** @return array{0: string, 1: float} [intent, confidence] */
    public static function classify(string $text, ?array $model = null): array
    {
        $model ??= self::model();
        $feats = array_filter(self::features($text), fn ($f) => isset($model['w'][$f]));

        if (!$model || !$feats) {
            return ['unknown', 0.0];
        }

        $p = self::probs($model, $feats);
        arsort($p);

        return [array_key_first($p), (float) reset($p)];
    }

    /** Tự học: đệm câu chắc chắn, đủ ROUND_SIZE thì chạy một vòng train. */
    private static function learn(string $intent, string $text): void
    {
        $path = self::modelPath();

        if (!is_file($path) || !($fp = fopen($path, 'c+'))) {
            return;
        }

        // ponytail: khóa toàn file mỗi lần học; đổi sang hàng đợi nếu lượng chat lớn.
        flock($fp, LOCK_EX);
        $model = json_decode(stream_get_contents($fp), true);
        $norm = self::normalize($text);

        if ($model && !in_array($norm, array_column($model['buffer'], 1), true)) {
            $model['buffer'][] = [$intent, $norm];

            if (count($model['buffer']) >= self::ROUND_SIZE) {
                $model = self::train($model, $model['buffer'], self::ROUND_EPOCHS, self::ROUND_LR);
                $model['buffer'] = [];
                $model['rounds']++;
                $model = self::compact($model);
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($model, JSON_UNESCAPED_UNICODE));
            self::$model = $model;
        }

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /* ======================= TRẢ LỜI ======================= */

    public static function reply(string $message, ?int $userId = null): array
    {
        $message = trim(Str::limit($message, 500, ''));
        $norm = self::normalize($message);

        if ($norm === '') {
            return self::say('Bạn hãy nhập câu hỏi để FAIA hỗ trợ nhé.');
        }

        if (!self::model()) {
            return self::say('FAIA chưa được huấn luyện. Vui lòng liên hệ quản trị viên.');
        }

        [$intent, $confidence] = self::classify($message);

        if ($confidence >= self::LEARN_CONFIDENCE) {
            self::learn($intent, $message);
        }

        $confident = $confidence >= self::MIN_CONFIDENCE;

        $catalog = self::catalog();
        $isSite = $confident && in_array($intent, self::SITE_INTENTS, true);
        $filters = self::parseFilters($norm, $catalog);
        $limit = $filters['limit'] ?? self::MAX_MOVIES;

        // 1. Tên phim có trong câu (trước ý định, để "căn phòng số 7" không bị coi là ngoài phạm vi).
        if (!in_array($intent, ['favorites', 'history'], true) || !$isSite) {
            if ($hits = self::matchTitles($norm, $catalog)) {
                return self::movieDetails($hits);
            }

            // 2. Đạo diễn / diễn viên có trong DB.
            if ($hits = self::matchPeople($norm, $catalog)) {
                return self::say('Các phim có người bạn hỏi trong FILM.SYS:', array_slice($hits, 0, $limit));
            }
        }

        // 3. Bộ lọc thể loại / năm.
        if ($filters['genres'] || $filters['exclude'] || $filters['year']) {
            return self::filteredReply($filters, $catalog);
        }

        // 4. Tên riêng viết hoa trong câu hỏi về phim mà kho không có → báo "chưa có", không bịa.
        if ($missing = self::missingEntity($message, $norm)) {
            return $missing;
        }

        // 5. Ý định về chức năng website.
        if ($isSite) {
            return self::siteReply($intent, $userId);
        }

        if ($confident) {
            switch ($intent) {
                case 'recommend_hot':
                    return self::ranked($catalog, $filters['sort'] ?? 'hot', 'Đây là các phim đang hot trên FILM.SYS:', $limit);
                case 'recommend_top':
                    return self::ranked($catalog, 'top', 'Các phim được đánh giá cao nhất hiện có:', $limit);
                case 'recommend_new':
                    return self::ranked($catalog, 'new', 'Các phim mới nhất trong kho:', $limit);
                case 'search_title':
                    return self::searchTitle($message, $catalog);
                case 'recommend_genre':
                    return self::siteReply('list_genres', $userId);
            }
        }

        // Không chắc ý định: thử coi cả câu là tên phim.
        $query = self::extractQuery($message);

        if (mb_strlen($query) >= 3 && ($hits = self::looseSearch(self::normalize($query), $catalog))) {
            return self::movieDetails($hits);
        }

        // Chỉ gõ "phim", "xem phim"... → gợi ý phim hot.
        if (($query === '' || $filters['sort']) && preg_match('/\bphim\b/', $norm)) {
            return self::ranked($catalog, $filters['sort'] ?? 'hot', 'Đây là các phim đang hot trên FILM.SYS:', $limit);
        }

        return self::say(
            'FAIA chưa hiểu ý bạn. Bạn có thể hỏi: "gợi ý phim hot", "có phim kinh dị không", '
            . '"tìm phim Robot", "phim yêu thích của tôi" hoặc "cách đặt lại mật khẩu".'
        );
    }

    private static function siteReply(string $intent, ?int $userId): array
    {
        $loginActions = [
            ['label' => 'Đăng nhập', 'url' => route('login')],
            ['label' => 'Đăng ký', 'url' => route('register')],
        ];

        switch ($intent) {
            case 'greet':
                return self::say('Xin chào! Mình là FAIA, trợ lý của FILM.SYS. Bạn muốn xem phim gì hôm nay?');
            case 'thanks':
                return self::say('Không có gì! Chúc bạn xem phim vui vẻ 🎬');
            case 'bye':
                return self::say('Tạm biệt! Hẹn gặp lại bạn trên FILM.SYS.');
            case 'identity':
                return self::say('Mình là FAIA — Film.sys Artificial Intelligence Assistant. Mình gợi ý phim có sẵn trong kho FILM.SYS và hướng dẫn bạn dùng các chức năng của website.');
            case 'capabilities':
                return self::say("FAIA có thể:\n• Gợi ý phim hot, phim mới, phim đánh giá cao\n• Tìm phim theo tên, thể loại, đạo diễn, diễn viên\n• Xem nhanh phim yêu thích và lịch sử xem của bạn\n• Hướng dẫn đăng nhập, đăng ký, quên mật khẩu, bình luận, đánh giá phim\nMình chỉ hỗ trợ trong phạm vi FILM.SYS.");
            case 'list_genres':
                $genres = collect(self::catalog())->pluck('genre_parts')->flatten()->unique()->sort()->values();

                return self::say(
                    $genres->isEmpty()
                        ? 'Kho phim hiện chưa có thể loại nào.'
                        : 'Các thể loại đang có: ' . $genres->implode(', ') . '.'
                );
            case 'favorites':
            case 'history':
                return self::personalList($intent, $userId, $loginActions);
            case 'login':
                return self::say('Bấm nút ĐĂNG NHẬP ở góc trên bên phải, nhập tên đăng nhập hoặc email cùng mật khẩu.', [], [['label' => 'Đến trang đăng nhập', 'url' => route('login')]]);
            case 'register':
                return self::say('Bấm ĐĂNG KÝ, nhập email, tên đăng nhập (3–50 ký tự) và mật khẩu (tối thiểu 6 ký tự). Đăng ký xong bạn đăng nhập được ngay.', [], [['label' => 'Đến trang đăng ký', 'url' => route('register')]]);
            case 'forgot_password':
                return self::say('Vào trang Quên mật khẩu, nhập email đã đăng ký. FILM.SYS sẽ gửi mã OTP 6 số (hiệu lực 5 phút) để bạn đặt mật khẩu mới.', [], [['label' => 'Quên mật khẩu', 'url' => route('password.forgot')]]);
            case 'logout':
                return self::say('Bấm nút ĐĂNG XUẤT ở góc trên bên phải màn hình.');
            case 'comment_help':
                return self::say('Mở trang phim, kéo xuống phần bình luận, chọn số sao (1–5), viết nội dung rồi bấm GỬI BÌNH LUẬN. Bạn cần đăng nhập để bình luận và có thể xóa bình luận của chính mình.', [], session('user_logged_in') ? [] : $loginActions);
            case 'watch_help':
                return self::say('Chọn một phim để mở trang xem, bấm nút ▶ trên trình phát. Nếu video không chạy, hãy tải lại trang hoặc thử phim khác. Nút "THÊM VÀO DANH SÁCH" để lưu phim yêu thích, nút "TẢI XUỐNG" để tải video (nếu có).');
            default: // out_of_scope
                return self::say('Xin lỗi, FAIA chỉ hỗ trợ các câu hỏi về phim và chức năng trên FILM.SYS thôi nhé.');
        }
    }

    private static function personalList(string $intent, ?int $userId, array $loginActions): array
    {
        $isFav = $intent === 'favorites';
        $label = $isFav ? 'phim yêu thích' : 'lịch sử xem';

        if (!$userId) {
            return self::say("Bạn cần đăng nhập để xem {$label}.", [], $loginActions);
        }

        $rows = DB::table($isFav ? 'user_favorites as x' : 'watch_history as x')
            ->join('movies as m', 'x.movie_id', '=', 'm.id')
            ->where('x.user_id', $userId)
            ->where('m.status', 1)
            ->orderByDesc($isFav ? 'x.created_at' : 'x.watched_at')
            ->limit(self::MAX_MOVIES)
            ->select('m.*')
            ->get();

        $action = [['label' => 'Xem tất cả', 'url' => route($isFav ? 'favorites.index' : 'history.index')]];

        if ($rows->isEmpty()) {
            return self::say(
                $isFav
                    ? 'Danh sách yêu thích của bạn đang trống. Mở một phim và bấm "☆ THÊM VÀO DANH SÁCH" để lưu nhé.'
                    : 'Bạn chưa xem phim nào. Hãy thử hỏi FAIA "gợi ý phim hot".',
                [],
                $action
            );
        }

        return self::say(
            $isFav ? 'Phim yêu thích của bạn:' : 'Các phim bạn xem gần đây:',
            $rows->map(fn ($m) => self::card(self::catalogRow($m)))->all(),
            $action
        );
    }

    /* ======================= DỮ LIỆU PHIM (DATABASE) ======================= */

    private static function catalog(): array
    {
        return DB::table('movies')->where('status', 1)->orderByDesc('id')->get()
            ->map(fn ($m) => self::catalogRow($m))->all();
    }

    private static function catalogRow(object $m): array
    {
        $cast = json_decode($m->cast ?? '[]', true);
        $title = (string) $m->title;
        $aliases = array_unique(array_filter([
            self::normalize($title),
            ...array_map(fn ($part) => self::normalize($part), preg_split('/[:\-–—]/u', $title)),
        ]));

        return [
            'row' => $m,
            'ntitle' => self::normalize($title),
            'aliases' => $aliases,
            'genre_parts' => array_values(array_filter(array_map('trim', preg_split('/[\/,]/u', (string) $m->genre)))),
            'ngenre' => self::normalize((string) $m->genre),
            'people' => array_values(array_filter(array_map(
                fn ($p) => self::normalize((string) $p),
                array_merge([$m->director ?? ''], is_array($cast) ? $cast : [])
            ))),
        ];
    }

    private static function card(array $c): array
    {
        $m = $c['row'];

        return [
            'id' => (int) $m->id,
            'title' => $m->title,
            'poster' => $m->poster,
            'genre' => $m->genre,
            'year' => $m->year,
            'rating' => $m->rating,
            'description' => Str::limit(trim(strip_tags((string) ($m->description ?: $m->tagline))), 110),
            'watch_url' => route('movies.show', $m->id),
        ];
    }

    /**
     * Dò tên phim trong câu: khớp nguyên tên/phần trước dấu ":" , hoặc một cụm 2 từ liền nhau
     * của tên phim, hoặc một từ đặc trưng (>= 5 ký tự) chỉ xuất hiện trong đúng một tên phim.
     */
    private static function matchTitles(string $norm, array $catalog): array
    {
        $hay = " {$norm} ";
        $stop = array_flip(array_merge(self::STOP, self::COMMON_WORDS));
        $tokenOwners = [];

        foreach ($catalog as $i => $c) {
            foreach (array_unique(explode(' ', $c['ntitle'])) as $t) {
                $tokenOwners[$t][] = $i;
            }
        }

        $hits = [];

        foreach ($catalog as $i => $c) {
            foreach ($c['aliases'] as $alias) {
                if (mb_strlen($alias) >= 3 && str_contains($hay, " {$alias} ")) {
                    $hits[$i] = $c;
                    continue 2;
                }
            }

            $tokens = explode(' ', $c['ntitle']);

            for ($k = 0; $k < count($tokens) - 1; $k++) {
                [$x, $y] = [$tokens[$k], $tokens[$k + 1]];

                if (!isset($stop[$x]) && !isset($stop[$y]) && str_contains($hay, " {$x} {$y} ")) {
                    $hits[$i] = $c;
                    continue 2;
                }
            }

            foreach ($tokens as $t) {
                // ponytail: heuristic từ đặc trưng; thêm từ phổ thông vào COMMON_WORDS nếu bắt nhầm.
                if (strlen($t) >= 5 && !isset($stop[$t]) && count($tokenOwners[$t]) === 1 && str_contains($hay, " {$t} ")) {
                    $hits[$i] = $c;
                    continue 2;
                }
            }
        }

        return array_values($hits);
    }

    private static function matchPeople(string $norm, array $catalog): array
    {
        $hay = " {$norm} ";

        return array_values(array_map(
            fn ($c) => self::card($c),
            array_slice(array_filter($catalog, function ($c) use ($hay) {
                foreach ($c['people'] as $p) {
                    if (strlen($p) >= 5 && str_contains($hay, " {$p} ")) {
                        return true;
                    }
                }

                return false;
            }), 0, self::MAX_MOVIES)
        ));
    }

    /**
     * Tách bộ lọc từ câu hỏi: thể loại muốn xem / loại trừ, năm, số lượng (top N), kiểu sắp xếp.
     * Thể loại gồm cả thể loại có trong DB lẫn thể loại phổ biến (để báo "chưa có").
     */
    private static function parseFilters(string $norm, array $catalog): array
    {
        $tokens = explode(' ', $norm);
        $available = collect($catalog)->pluck('genre_parts')->flatten()->unique()
            ->mapWithKeys(fn ($g) => [self::normalize($g) => $g])->all();

        // pattern => [needle tìm trong thể loại phim, nhãn hiển thị]
        $patterns = [];

        foreach (self::KNOWN_GENRES as $p => $label) {
            $patterns[$p] = [$p === 'phim hai' ? 'hai' : $p, $label];
        }

        foreach ($available as $p => $label) {
            $patterns[$p] = [$p, $label];
        }

        foreach (self::GENRE_ALIASES as $p => $target) {
            $patterns[$p] = [$target === 'phim hai' ? 'hai' : $target, $available[$target] ?? self::KNOWN_GENRES[$target] ?? $target];
        }

        uksort($patterns, fn ($a, $b) => substr_count($b, ' ') <=> substr_count($a, ' ') ?: strlen($b) <=> strlen($a));

        $used = [];
        $genres = [];
        $exclude = [];

        foreach ($patterns as $p => [$needle, $label]) {
            $pt = explode(' ', $p);
            $n = count($pt);

            for ($i = 0; $i + $n <= count($tokens); $i++) {
                if (array_slice($tokens, $i, $n) !== $pt || array_intersect(range($i, $i + $n - 1), $used)) {
                    continue;
                }

                $used = array_merge($used, range($i, $i + $n - 1));
                $before = ' ' . implode(' ', array_slice($tokens, max(0, $i - 3), min(3, $i))) . ' ';
                $negated = (bool) preg_match('/ (tru|ngoai|ghet|bo qua|khong phai|khong thich|khong muon|khong xem|chan) /', $before);
                $entry = ['needle' => $needle, 'label' => $label, 'pos' => $i];

                if ($negated) {
                    $exclude[$needle] = $entry;
                } else {
                    $genres[$needle] = $entry;
                }
            }
        }

        // "kinh dị hoặc hoạt hình", "kinh dị hay hành động" → hợp; còn lại ưu tiên giao.
        $union = false;

        if (count($genres) >= 2) {
            $pos = array_column($genres, 'pos');
            $between = ' ' . implode(' ', array_slice($tokens, min($pos), max($pos) - min($pos))) . ' ';
            $union = (bool) preg_match('/ (hoac|hay) /', $between);
        }

        $limit = null;

        if (preg_match('/\btop (\d{1,2})\b/', $norm, $m) || preg_match('/\b(\d{1,2}) (bo )?phim\b/', $norm, $m)) {
            $limit = max(1, min(8, (int) $m[1]));
        }

        $sort = null;

        if (preg_match('/\b(moi nhat|moi ra|moi ve|moi cap nhat|gan day|vua ra)\b/', $norm)) {
            $sort = 'new';
        } elseif (preg_match('/\b(diem cao|danh gia cao|hay nhat|rating|nhieu sao|cao nhat|xuat sac|tuyet voi|dinh nhat|hap dan)\b/', $norm)) {
            $sort = 'top';
        }

        preg_match('/\b(19\d{2}|20\d{2})\b/', $norm, $year);

        return [
            'genres' => array_values($genres),
            'exclude' => array_values($exclude),
            'union' => $union,
            'year' => isset($year[1]) ? (int) $year[1] : null,
            'limit' => $limit,
            'sort' => $sort,
        ];
    }

    private static function filteredReply(array $f, array $catalog): array
    {
        $hasGenre = fn ($c, $needle) => str_contains(" {$c['ngenre']} ", " {$needle} ");
        $missing = [];
        $wanted = [];

        foreach ($f['genres'] as $g) {
            if (array_filter($catalog, fn ($c) => $hasGenre($c, $g['needle']))) {
                $wanted[] = $g;
            } else {
                $missing[] = $g['label'];
            }
        }

        $missingText = $missing ? 'Hiện FILM.SYS chưa có phim thể loại ' . implode(', ', $missing) . '.' : '';

        // Hỏi toàn thể loại chưa có → chỉ báo chưa có.
        if ($f['genres'] && !$wanted) {
            return self::say($missingText, [], [['label' => 'Xem các thể loại đang có', 'url' => route('movies.index')]]);
        }

        $movies = $catalog;

        if ($wanted) {
            $all = array_filter($catalog, fn ($c) => !array_filter($wanted, fn ($g) => !$hasGenre($c, $g['needle'])));
            $any = array_filter($catalog, fn ($c) => (bool) array_filter($wanted, fn ($g) => $hasGenre($c, $g['needle'])));
            $movies = (!$f['union'] && $all) ? $all : $any;
        }

        foreach ($f['exclude'] as $g) {
            $movies = array_filter($movies, fn ($c) => !$hasGenre($c, $g['needle']));
        }

        if ($f['year']) {
            $movies = array_filter($movies, fn ($c) => (int) $c['row']->year === $f['year']);
        }

        $labels = implode(' / ', array_column($wanted, 'label'));
        $what = trim(($labels ? "phim {$labels}" : 'phim') . ($f['year'] ? " năm {$f['year']}" : ''));
        $what .= $f['exclude'] ? ' (không gồm ' . implode(', ', array_column($f['exclude'], 'label')) . ')' : '';

        if (!$movies) {
            return self::say(trim("{$missingText} Hiện FILM.SYS chưa có {$what}."), [], [
                ['label' => 'Xem kho phim', 'url' => route('movies.index')],
            ]);
        }

        $reply = self::ranked(array_values($movies), $f['sort'] ?? 'top', trim("{$missingText} " . ucfirst($what) . ' đang có trên FILM.SYS:'), $f['limit'] ?? self::MAX_MOVIES);

        if (count($wanted) === 1) {
            $reply['actions'][] = ['label' => 'Xem thêm', 'url' => route('movies.index', ['search' => $wanted[0]['label']])];
        }

        return $reply;
    }

    /** Câu hỏi về phim có tên riêng viết hoa (Titanic, Leonardo DiCaprio...) mà kho không có. */
    private static function missingEntity(string $message, string $norm): ?array
    {
        if (!preg_match('/\b(phim|xem|dong|dao dien|dien vien)\b/', $norm)) {
            return null;
        }

        $words = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\p{N}\s.]+/u', ' ', $message)));
        if (mb_strtoupper($message) === $message) {
            return null;
        }

        $names = [];
        $words[] = '';

        for ($i = 1, $start = null; $i < count($words); $i++) {
            $isName = preg_match('/^\p{Lu}/u', $words[$i]) && !in_array(self::normalize($words[$i]), ['faia', 'film', 'sys', 'otp'], true);

            if ($isName) {
                $start ??= $i;
                continue;
            }

            // Chỉ nhận tên đứng ngay sau "phim/xem/bộ/của/có" hoặc ngay trước "đóng".
            if ($start !== null) {
                $prev = self::normalize($words[$start - 1]);
                $next = self::normalize($words[$i]);

                if (in_array($prev, ['phim', 'xem', 'bo', 'cua', 'co'], true) || $next === 'dong') {
                    $names[] = rtrim(implode(' ', array_slice($words, $start, $i - $start)), '.');
                }

                $start = null;
            }
        }

        if (!$names) {
            return null;
        }

        $name = $names[0];
        $isPerson = preg_match('/\b(dong|dao dien|dien vien|phim cua)\b/', $norm);

        return self::say(
            $isPerson
                ? "Hiện FILM.SYS chưa có phim nào của/có {$name}."
                : "Hiện FILM.SYS chưa có phim \"{$name}\". Bạn có thể hỏi FAIA \"gợi ý phim hot\" để xem các phim đang có.",
            [],
            [['label' => 'Xem kho phim', 'url' => route('movies.index')]]
        );
    }

    private static function ranked(array $catalog, string $mode, string $text, int $limit = self::MAX_MOVIES): array
    {
        if (!$catalog) {
            return self::say('Kho phim hiện chưa có phim nào.');
        }

        usort($catalog, match ($mode) {
            'hot' => fn ($a, $b) => [$b['row']->featured, $b['row']->rating] <=> [$a['row']->featured, $a['row']->rating],
            'top' => fn ($a, $b) => $b['row']->rating <=> $a['row']->rating,
            default => fn ($a, $b) => [$b['row']->year, $b['row']->id] <=> [$a['row']->year, $a['row']->id],
        });

        return self::say($text, array_map(fn ($c) => self::card($c), array_slice($catalog, 0, $limit)));
    }

    /** Bỏ từ đệm, giữ nguyên chữ gốc (có dấu) của phần tên phim. */
    private static function extractQuery(string $message): string
    {
        $words = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $message)));
        $stop = array_flip(self::STOP);

        return trim(implode(' ', array_filter($words, fn ($w) => $w !== '' && !isset($stop[self::normalize($w)]))));
    }

    private static function looseSearch(string $nq, array $catalog): array
    {
        if ($nq === '') {
            return [];
        }

        $qTokens = explode(' ', $nq);

        return array_values(array_filter($catalog, function ($c) use ($nq, $qTokens) {
            if (str_contains(" {$c['ntitle']} ", " {$nq} ")) {
                return true;
            }

            $tTokens = explode(' ', $c['ntitle']);
            $hit = count(array_intersect($qTokens, $tTokens));

            return $hit >= 2 && $hit / count($qTokens) >= 0.6;
        }));
    }

    private static function searchTitle(string $message, array $catalog): array
    {
        $query = self::extractQuery($message);

        if ($query === '') {
            return self::say('Bạn muốn tìm phim tên gì? Hãy gõ tên phim nhé.');
        }

        if ($hits = self::looseSearch(self::normalize($query), $catalog)) {
            return self::movieDetails($hits);
        }

        return self::say("Hiện FILM.SYS chưa có phim \"{$query}\". Bạn có thể hỏi FAIA \"gợi ý phim hot\" để xem các phim đang có.", [], [
            ['label' => 'Xem kho phim', 'url' => route('movies.index')],
        ]);
    }

    private static function movieDetails(array $hits): array
    {
        $hits = array_slice($hits, 0, self::MAX_MOVIES);

        if (count($hits) > 1) {
            return self::say('FAIA tìm thấy ' . count($hits) . ' phim phù hợp:', array_map(fn ($c) => self::card($c), $hits));
        }

        $m = $hits[0]['row'];
        $cast = json_decode($m->cast ?? '[]', true);
        $parts = array_filter([
            "\"{$m->title}\"" . ($m->year ? " ({$m->year})" : ''),
            $m->genre ? "Thể loại: {$m->genre}" : null,
            $m->rating ? "Đánh giá: ★ {$m->rating}" : null,
            $m->duration ? "Thời lượng: {$m->duration}" : null,
            $m->director ? "Đạo diễn: {$m->director}" : null,
            is_array($cast) && $cast ? 'Diễn viên: ' . implode(', ', $cast) : null,
        ]);

        return self::say('Có trên FILM.SYS! ' . implode(' · ', $parts), [self::card($hits[0])]);
    }

    private static function say(string $answer, array $movies = [], array $actions = []): array
    {
        return ['answer' => $answer, 'movies' => array_values($movies), 'actions' => $actions];
    }
}
