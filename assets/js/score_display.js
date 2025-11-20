const scoreboard = document.getElementById('scoreboard');
const teamAScoreDisplay = document.getElementById('team-a-score-display');
const teamBScoreDisplay = document.getElementById('team-b-score-display');

// Update scores in real-time using LocalStorage
setInterval(() => {
  const teamAScore = localStorage.getItem('teamAScore');
  const teamBScore = localStorage.getItem('teamBScore');

  teamAScoreDisplay.textContent = teamAScore;
  teamBScoreDisplay.textContent = teamBScore;
}, 1000);