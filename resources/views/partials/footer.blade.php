
<button class="ai-chat-launcher"
        id="aiChatLauncher"
        type="button"
        aria-label="Mở FAIA">
    <span class="ai-chat-launcher__pulse"></span>
    <span class="ai-chat-launcher__icon">✦</span>
    <span class="ai-chat-launcher__label">FAIA</span>
</button>

<aside class="ai-chatbox"
       id="aiChatbox"
       aria-label="FAIA"
       aria-hidden="true">
    <div class="ai-chatbox__header">
        <div>
            <span class="ai-chatbox__eyebrow">FILM.SYS ARTIFICIAL INTELLIGENCE ASSISTANT</span>
            <h2>FAIA</h2>
        </div>
        <button class="ai-chatbox__close"
                id="aiChatClose"
                type="button"
                aria-label="Đóng FAIA">×</button>
    </div>

    <div class="ai-chatbox__messages" id="aiChatMessages" aria-live="polite">
        <div class="ai-chat-message ai-chat-message--assistant">
            <span class="ai-chat-message__avatar">✦</span>
            @if (session('user_logged_in') && session('user_id'))
            <p>Xin chào {{ session('user_name') }}, mình là FAIA! Mình gợi ý phim đang có trên FILM.SYS, tìm phim theo tên, thể loại, diễn viên và hướng dẫn bạn dùng website.</p>
            @else
            <div>
                <p>Xin chào, mình là FAIA! Bạn cần đăng nhập để trò chuyện với FAIA.</p>
                <div class="ai-chat-actions">
                    <a href="{{ route('login') }}">Đăng nhập</a>
                    <a href="{{ route('register') }}">Đăng ký</a>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if (session('user_logged_in') && session('user_id'))
    <div class="ai-chatbox__quick-actions">
        <button type="button"
                data-ai-prompt="Gợi ý phim hot">
            Phim hot
        </button>
        <button type="button"
                data-ai-prompt="Tìm phim theo thể loại hành động">
            Phim hành động
        </button>
        <button type="button"
                data-ai-prompt="Phim yêu thích của tôi">
            Yêu thích của tôi
        </button>
    </div>

    <form class="ai-chatbox__form" id="aiChatForm">
        @csrf
        <textarea id="aiChatInput"
                  rows="1"
                  maxlength="500"
                  placeholder="Hỏi FAIA về phim trên FILM.SYS..."
                  aria-label="Nhập câu hỏi"></textarea>
        <button type="submit" aria-label="Gửi tin nhắn">➤</button>
    </form>
    @endif
</aside>

<footer class="site-footer hud-panel">
    <div class="sovereignty-badge">
        <span class="sovereignty-badge__star">★</span>
        <span class="sovereignty-badge__text">
            Hoàng Sa &amp; Trường Sa là của Việt Nam!
        </span>
    </div>

    <div class="hud-corner hud-corner--tl"></div>
    <div class="hud-corner hud-corner--tr"></div>

    <div class="container site-footer__inner">
        <div class="footer-col footer-col--brand">
            <div class="brand brand--footer">
                <span class="brand__ring brand__ring--sm">
                    <span class="brand__core"></span>
                </span>
                <span class="brand__text">
                    FILM<span class="brand__text-accent">.SYS</span>
                </span>
            </div>

            <p class="footer-tagline">
                FILM.SYS / – Trung tâm truyền phát điện ảnh thế hệ mới,
                vận hành trên nền tảng phân tích dữ liệu thời gian thực và
                đồng bộ cùng mạng lưới phim toàn cầu. Truy xuất kho lưu trữ
                đa dạng từ phim chiếu rạp, phim bộ đến các tác phẩm bom tấn
                quốc tế (Việt Nam, Hàn Quốc, Trung Quốc, Âu Mỹ...) với độ
                phân giải chuẩn 4K Ultra HD. Trải nghiệm ngay giao diện
                giải trí tương lai vượt trội!
            </p>

            <div class="footer-status">
                <span class="status-dot"></span>
                TẤT CẢ HỆ THỐNG HOẠT ĐỘNG BÌNH THƯỜNG
            </div>
        </div>

        <div class="footer-col">
            <h4 class="footer-heading">// ĐIỀU HƯỚNG</h4>
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('home') }}#featured">Phim đề cử</a>
            <a href="{{ route('home') }}#grid">Kho dữ liệu phim</a>
            <a href="{{ route('home') }}#grid">Thể loại</a>
        </div>

        <div class="footer-col">
            <h4 class="footer-heading">// HỖ TRỢ</h4>
            <a href="#">Trung tâm trợ giúp</a>
            <a href="#">Báo lỗi liên kết</a>
            <a href="#">Điều khoản dịch vụ</a>
            <a href="#">Quyền riêng tư</a>
        </div>

        <div class="footer-col">
            <h4 class="footer-heading">// TỌA ĐỘ HỆ THỐNG</h4>
            <p class="mono-line">LAT/LNG: 10.7769° N, 106.7009° E</p>
            <p class="mono-line">CORE TEMP: 36.6°C — NOMINAL</p>
            <p class="mono-line">BUILD: STARK-SYS v4.7.0-MK47</p>
        </div>
    </div>

    <div class="site-footer__bottom">
        <span>
            © {{ date('Y') }} STARK INDUSTRIES — CINEMA DIVISION.
            ALL RIGHTS RESERVED.
        </span>
        <span class="mono-line">POWERED BY ARC REACTOR</span>
    </div>
</footer>
