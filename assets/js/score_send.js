const form = document.getElementById('score-form');

form.addEventListener('submit', (e) => {
  e.preventDefault();
  const teamAScore = document.getElementById('team-a-score').value;
  const teamBScore = document.getElementById('team-b-score').value;

  // Send scores to Page 2 using LocalStorage
  localStorage.setItem('teamAScore', teamAScore);
  localStorage.setItem('teamBScore', teamBScore);


});