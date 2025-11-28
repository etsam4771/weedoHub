// Dictionary App - AJAX & Infinite Scroll
let currentPage = 1;
let isLoading = false;
let hasMoreResults = true;
let currentSearch = '';
let debounceTimer = null;

// Text-to-Speech functionality
const speechSupported = 'speechSynthesis' in window;

function speak(text, lang = 'en-US') {
    if (!speechSupported) {
        console.log('Text-to-speech not supported');
        return;
    }
    
    // Cancel any ongoing speech
    window.speechSynthesis.cancel();
    
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = lang;
    utterance.rate = 0.9;
    utterance.pitch = 1;
    
    window.speechSynthesis.speak(utterance);
}

function createWordCard(word) {
    const card = document.createElement('div');
    card.className = 'word-card';
    card.innerHTML = `
        <div class="word-header">
            <div class="word-title-section">
                <h2>${escapeHtml(word.word_name)}</h2>
                ${speechSupported ? `<button class="btn-speak" onclick="speak('${escapeHtml(word.word_name)}', 'en-US')" title="Pronounce word">🔊</button>` : ''}
            </div>
            <div class="word-actions">
                <a href="edit_word.php?id=${word.id}" class="btn-edit">Edit</a>
                <a href="delete_word.php?id=${word.id}" 
                   class="btn-delete" 
                   onclick="return confirm('Are you sure you want to delete this word?')">Delete</a>
            </div>
        </div>
        
        <div class="meanings">
            <div class="meaning-section">
                <div class="meaning-header">
                    <span class="label">Hindi:</span>
                    ${speechSupported ? `<button class="btn-speak-mini" onclick="speak('${escapeHtml(word.meaning_hindi)}', 'hi-IN')" title="Listen to Hindi meaning">🔊</button>` : ''}
                </div>
                <span class="meaning">${escapeHtml(word.meaning_hindi)}</span>
            </div>
            <div class="meaning-section">
                <div class="meaning-header">
                    <span class="label">English:</span>
                    ${speechSupported ? `<button class="btn-speak-mini" onclick="speak('${escapeHtml(word.meaning_english)}', 'en-US')" title="Listen to English meaning">🔊</button>` : ''}
                </div>
                <span class="meaning">${escapeHtml(word.meaning_english)}</span>
            </div>
        </div>
        
        ${word.example ? `
            <div class="example">
                <div class="example-header">
                    <span class="label">Example:</span>
                    ${speechSupported ? `<button class="btn-speak-mini" onclick="speak('${escapeHtml(word.example)}', 'en-US')" title="Listen to example">🔊</button>` : ''}
                </div>
                <p>${escapeHtml(word.example)}</p>
            </div>
        ` : ''}
        
        <div class="word-footer">
            <a href="add_to_collection.php?word_id=${word.id}" class="btn-add-collection">
                + Add to Collection
            </a>
        </div>
    `;
    return card;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showLoading() {
    document.getElementById('loading').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

function loadWords(append = false) {
    if (isLoading || !hasMoreResults) return;
    
    isLoading = true;
    showLoading();
    
    const url = `api/get_words.php?page=${currentPage}&search=${encodeURIComponent(currentSearch)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('wordsContainer');
                
                if (!append) {
                    container.innerHTML = '';
                }
                
                if (data.words.length === 0 && !append) {
                    container.innerHTML = `
                        <div class="no-results">
                            <p>${currentSearch ? 'No words found. Try a different search.' : '<a href="add_word.php">Add your first word!</a>'}</p>
                        </div>
                    `;
                    hasMoreResults = false;
                } else {
                    data.words.forEach(word => {
                        container.appendChild(createWordCard(word));
                    });
                    
                    hasMoreResults = data.hasMore;
                    
                    if (!hasMoreResults && data.words.length > 0) {
                        document.getElementById('noMoreResults').style.display = 'block';
                    } else {
                        document.getElementById('noMoreResults').style.display = 'none';
                    }
                }
                
                document.getElementById('totalWords').textContent = data.total;
                
                isLoading = false;
                hideLoading();
            }
        })
        .catch(error => {
            console.error('Error loading words:', error);
            isLoading = false;
            hideLoading();
            
            if (!append) {
                document.getElementById('wordsContainer').innerHTML = `
                    <div class="no-results">
                        <p>Error loading words. Please refresh the page.</p>
                    </div>
                `;
            }
        });
}

// Search functionality with debouncing
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    performSearch();
});

document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        performSearch();
    }, 500); // Wait 500ms after user stops typing
});

function performSearch() {
    const searchValue = document.getElementById('searchInput').value.trim();
    currentSearch = searchValue;
    currentPage = 1;
    hasMoreResults = true;
    
    // Show/hide clear button
    document.getElementById('clearSearch').style.display = searchValue ? 'inline-block' : 'none';
    
    loadWords(false);
}

document.getElementById('clearSearch').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    currentSearch = '';
    currentPage = 1;
    hasMoreResults = true;
    this.style.display = 'none';
    loadWords(false);
});

// Infinite scroll
let scrollTimeout;
window.addEventListener('scroll', function() {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
        if (isLoading || !hasMoreResults) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const documentHeight = document.documentElement.scrollHeight;
        
        // Load more when user is 200px from bottom
        if (scrollPosition >= documentHeight - 200) {
            currentPage++;
            loadWords(true);
        }
    }, 100);
});

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    loadWords(false);
});
