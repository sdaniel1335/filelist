document.addEventListener('submit', function (event) {
  if (event.target.classList.contains('delete-file') && !window.confirm('Biztosan törlöd ezt a fájlt? A törlés végleges.')) {
    event.preventDefault();
  }
});
