// Collection View - AJAX & Infinite Scroll
let currentPage = 1;
let isLoading = false;
let hasMoreResults = true;

// Text-to-Speech functionality
const speechSupported = 'speechSynthesis' in window;

function speak(text, lang = 'en-US') {
    if (!speechSupported) {
        console.log('Text-to-speech not supported');
        return;
    }
    
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
                <a href="view_collection.php?id=${categoryId}&remove_word=${word.id}" 
                   class="btn-delete" 
                   onclick="return confirm('Remove this word from the collection?')">Remove</a>
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
    
    const url = `api/get_collection_words.php?category_id=${categoryId}&page=${currentPage}`;
    
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
                            <p>No words in this collection yet. <a href="index.php">Browse the dictionary</a> and add words to this collection!</p>
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
        });
}

// Infinite scroll
let scrollTimeout;
window.addEventListener('scroll', function() {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
        if (isLoading || !hasMoreResults) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const documentHeight = document.documentElement.scrollHeight;
        
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
