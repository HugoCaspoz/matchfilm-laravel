// Funcionalidad JavaScript para MatchFilm

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Sistema de valoración con estrellas
    const ratingInputs = document.querySelectorAll('.rating-input');
    const ratingStars = document.querySelectorAll('.rating-star');
    
    if (ratingStars.length > 0) {
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const ratingInput = this.closest('.rating-container').querySelector('.rating-input');
                ratingInput.value = value;
                
                // Actualizar estrellas
                const stars = this.closest('.rating-container').querySelectorAll('.rating-star');
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
        });
    }
    
    // Confirmación para eliminar
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Cargar más películas al hacer scroll
    const movieContainer = document.getElementById('movie-container');
    const loadMoreBtn = document.getElementById('load-more');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const page = parseInt(this.getAttribute('data-page')) + 1;
            const url = this.getAttribute('data-url').replace('PAGE_NUMBER', page);
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        // Renderizar nuevas películas
                        data.results.forEach(movie => {
                            const movieCard = createMovieCard(movie);
                            movieContainer.appendChild(movieCard);
                        });
                        
                        // Actualizar página actual
                        this.setAttribute('data-page', page);
                        
                        // Ocultar botón si es la última página
                        if (page >= data.total_pages) {
                            this.style.display = 'none';
                        }
                    } else {
                        this.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error cargando más películas:', error);
                });
        });
    }
    
    // Función para crear tarjeta de película
    function createMovieCard(movie) {
        const col = document.createElement('div');
        col.className = 'col-md-3 mb-4';
        
        const card = document.createElement('div');
        card.className = 'card h-100';
        
        let imageHtml = '';
        if (movie.poster_path) {
            imageHtml = `<img src="https://image.tmdb.org/t/p/w500${movie.poster_path}" class="card-img-top" alt="${movie.title}">`;
        } else {
            imageHtml = `<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 300px;">
                <span class="text-white">Sin imagen</span>
            </div>`;
        }
        
        const overview = movie.overview ? movie.overview.substring(0, 100) + '...' : '';
        
        card.innerHTML = `
            ${imageHtml}
            <div class="card-body">
                <h5 class="card-title">${movie.title}</h5>
                <p class="card-text small">${overview}</p>
            </div>
            <div class="card-footer bg-white">
                <a href="/movies/${movie.id}" class="btn btn-primary w-100">Ver detalles</a>
            </div>
        `;
        
        col.appendChild(card);
        return col;
    }
});