
@php
    $value = fn ($key, $default = '') => old(
        $key,
        $movie ? ($movie->{$key} ?? $default) : $default
    );

    $castValue = $movie
        ? (is_array($movie->cast)
            ? implode(', ', $movie->cast)
            : implode(', ', json_decode($movie->cast ?? '[]', true) ?: []))
        : '';
@endphp

<label>Tên phim *</label>
<input name="title" required maxlength="255" value="{{ $value('title') }}"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Thể loại</label>
<input name="genre" value="{{ $value('genre') }}" placeholder="Ví dụ: Hành động, Khoa học viễn tưởng"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px;">
    <div style="display: grid; gap: 8px;">
        <label>Năm phát hành</label>
        <input type="number" name="year" min="1888" max="2100" value="{{ $value('year') }}"
               style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">
    </div>

    <div style="display: grid; gap: 8px;">
        <label>Thời lượng (phút)</label>
        <input type="number" name="duration" min="1" value="{{ $value('duration') }}"
               style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">
    </div>

    <div style="display: grid; gap: 8px;">
        <label>Đánh giá (0–10)</label>
        <input type="number" name="rating" min="0" max="10" step="0.1" value="{{ $value('rating', '0') }}"
               style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">
    </div>
</div>

<label>Đạo diễn</label>
<input name="director" value="{{ $value('director') }}"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Dàn diễn viên (ngăn cách bằng dấu phẩy)</label>
<input name="cast" value="{{ old('cast', $castValue) }}" placeholder="Tên diễn viên 1, Tên diễn viên 2"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Tagline</label>
<input name="tagline" value="{{ $value('tagline') }}"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Mô tả phim</label>
<textarea name="description" rows="5"
          style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">{{ $value('description') }}</textarea>

<label>Đường dẫn poster</label>
<input name="poster" value="{{ $value('poster') }}" placeholder="URL ảnh hoặc đường dẫn ảnh hiện có"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Đường dẫn video phim</label>
<input name="video_url" value="{{ $value('video_url') }}"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Đường dẫn trailer</label>
<input name="trailer_url" value="{{ $value('trailer_url') }}"
       style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">

<label>Trạng thái hiển thị</label>
<select name="status" required
        style="padding: 12px; background: #0b1220; color: white; border: 1px solid #34465a; border-radius: 6px;">
    <option value="1" {{ (string) $value('status', '1') === '1' ? 'selected' : '' }}>Đang hiển thị</option>
    <option value="0" {{ (string) $value('status', '1') === '0' ? 'selected' : '' }}>Đang ẩn</option>
</select>

<label style="display: flex; gap: 10px; align-items: center;">
    <input type="checkbox" name="featured" value="1"
           {{ old('featured', $movie->featured ?? false) ? 'checked' : '' }}>
    Đánh dấu phim nổi bật
</label>

<button type="submit"
        style="padding: 13px; margin-top: 10px; background: #06394a; border: 1px solid #00d9ff; color: #00e5ff; border-radius: 6px; font-weight: bold; cursor: pointer;">
    {{ $buttonText }}
</button>
