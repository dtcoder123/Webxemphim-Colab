document.addEventListener('DOMContentLoaded', () => {
  const wall = document.querySelector('[data-auth-drift-wall]');
  if (!wall) return;

  const images = [
    'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1485846234645-a62644f84728?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1574267432644-f610f7a8d6d5?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1598899134739-24c46f58b8c0?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1440404653325-ab127d49?q=80&w=800&auto=format&fit=crop'
  ];
  const plane = document.createElement('div');
  plane.className = 'auth-drift-wall__plane';

  for (let columnIndex = 0; columnIndex < 5; columnIndex += 1) {
    const column = document.createElement('div');
    column.className = 'auth-drift-wall__column';
    const track = document.createElement('div');
    track.className = 'auth-drift-wall__track';
    track.style.setProperty('--drift-time', `${24 + columnIndex * 2.5}s`);
    track.style.setProperty('--drift-delay', `${-columnIndex * 3.7}s`);

    for (let copyIndex = 0; copyIndex < 2; copyIndex += 1) {
      images.forEach((image, imageIndex) => {
        const tile = document.createElement('div');
        tile.className = 'auth-drift-wall__tile';
        tile.style.setProperty('--drift-roll', `${((columnIndex * 7 + imageIndex * 3) % 7) - 3}deg`);
        const poster = document.createElement('img');
        poster.src = image;
        poster.alt = '';
        poster.loading = 'lazy';
        poster.decoding = 'async';
        tile.appendChild(poster);
        track.appendChild(tile);
      });
    }

    column.appendChild(track);
    plane.appendChild(column);
  }

  wall.appendChild(plane);
});