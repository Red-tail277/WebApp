const rootShell = document.querySelector('[data-game-mode]');
const modeKey = rootShell ? rootShell.dataset.gameMode : null;
const gameRoot = document.getElementById('gameRoot');
const scoreDisplay = document.getElementById('scoreDisplay');

let activeScenario = null;
let selectedLetters = [];
let selectedTile = '';
let crosswordLetters = [];
let answered = false;

function htmlEscape(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

async function apiGet(url) {
  const response = await fetch(url, { credentials: 'same-origin' });
  if (!response.ok) throw new Error('Request failed');
  return response.json();
}

async function apiPost(url, payload) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });
  if (!response.ok) throw new Error('Request failed');
  return response.json();
}

function setFeedback(message, type = '') {
  const feedback = document.getElementById('feedback');
  if (!feedback) return;
  feedback.className = 'feedback';
  if (type) feedback.classList.add(type);
  feedback.innerHTML = message;
}

function updateScore(score) {
  if (scoreDisplay) scoreDisplay.textContent = score;
}

async function loadScenario() {
  try {
    gameRoot.innerHTML = '<div class="game-panel"><p>Loading scenario...</p></div>';
    const data = await apiGet(`api/get_scenario.php?mode=${encodeURIComponent(modeKey)}`);

    if (!data.success) {
      gameRoot.innerHTML = `<div class="game-panel"><h2>${htmlEscape(data.message || 'No scenario found.')}</h2><a class="btn btn-primary" href="dashboard.php">Back to Dashboard</a></div>`;
      return;
    }

    activeScenario = data.scenario;
    selectedLetters = [];
    selectedTile = '';
    crosswordLetters = [];
    answered = false;
    updateScore(data.total_score);

    if (modeKey === 'tile_selection') renderTileMode(data);
    if (modeKey === 'one_word') renderWordMode(data, false);
    if (modeKey === 'crossword') renderCrosswordMode(data);
  } catch (error) {
    gameRoot.innerHTML = '<div class="game-panel"><h2>Something went wrong.</h2><p>Please check your XAMPP Apache and MySQL connection.</p></div>';
  }
}

function progressMarkup(progress) {
  return `
    <div class="progress-wrap">
      <div class="progress-label">
        <span>${progress.completed} of ${progress.total} completed</span>
        <span>${progress.percent}%</span>
      </div>
      <div class="progress-bar"><div class="progress-fill" style="width:${progress.percent}%"></div></div>
    </div>
  `;
}

function renderClues(clues) {
  return `
    <section class="clues" aria-label="Scenario clues">
      ${clues.map((clue, index) => `
        <article class="clue-card">
          <div class="clue-icon">${index + 1}</div>
          <h3>${htmlEscape(clue.clue_title)}</h3>
          <p>${htmlEscape(clue.clue_text)}</p>
        </article>
      `).join('')}
    </section>
  `;
}

function renderWordMode(data, crossword = false) {
  const scenario = data.scenario;
  const boxes = Array.from({ length: scenario.answer_length }, (_, i) => selectedLetters[i]?.letter || '');
  const letterButtons = scenario.letter_bank.map((letter, index) => {
    const used = selectedLetters.some(item => item.index === index);
    return `<button class="letter-btn" data-index="${index}" ${used || answered ? 'disabled' : ''}>${htmlEscape(letter)}</button>`;
  }).join('');

  gameRoot.innerHTML = `
    <section class="game-panel level-info">
      <div>
        <h2>${htmlEscape(scenario.title)}</h2>
        <p>${htmlEscape(scenario.prompt)}</p>
      </div>
      <div class="badge">${htmlEscape(scenario.mode_name)}</div>
    </section>
    ${progressMarkup(data.progress)}
    ${renderClues(scenario.clues)}
    <section class="game-panel answer-area">
      <div class="${crossword ? 'crossword-grid' : 'answer-boxes'}">
        ${boxes.map(letter => `<div class="${crossword ? 'crossword-cell' : 'answer-box'}">${htmlEscape(letter)}</div>`).join('')}
      </div>
      <div class="letter-bank">${letterButtons}</div>
      <div class="controls">
        <button class="btn btn-green" id="submitAnswer">Submit</button>
        <button class="btn btn-gray" id="backspaceAnswer">Backspace</button>
        <button class="btn btn-gray" id="clearAnswer">Clear</button>
        <button class="btn btn-gold" id="hintAnswer">Hint</button>
        <button class="btn btn-primary" id="nextAnswer">Next</button>
      </div>
      <div class="feedback" id="feedback">Choose letters to form the answer. Take your time.</div>
    </section>
  `;

  document.querySelectorAll('.letter-btn').forEach(button => {
    button.addEventListener('click', () => addLetter(button.textContent, Number(button.dataset.index), crossword));
  });
  document.getElementById('submitAnswer').addEventListener('click', submitWordAnswer);
  document.getElementById('backspaceAnswer').addEventListener('click', () => removeLetter(crossword));
  document.getElementById('clearAnswer').addEventListener('click', () => clearLetters(crossword));
  document.getElementById('hintAnswer').addEventListener('click', () => setFeedback(`Hint: ${htmlEscape(activeScenario.hint)}`));
  document.getElementById('nextAnswer').addEventListener('click', loadScenario);
}

function addLetter(letter, index, crossword) {
  if (answered || selectedLetters.length >= activeScenario.answer_length) return;
  selectedLetters.push({ letter, index });
  renderWordMode({ scenario: activeScenario, progress: activeScenario.progress, total_score: Number(scoreDisplay.textContent) }, crossword);
}

function removeLetter(crossword) {
  if (answered) return;
  selectedLetters.pop();
  renderWordMode({ scenario: activeScenario, progress: activeScenario.progress, total_score: Number(scoreDisplay.textContent) }, crossword);
}

function clearLetters(crossword) {
  if (answered) return;
  selectedLetters = [];
  renderWordMode({ scenario: activeScenario, progress: activeScenario.progress, total_score: Number(scoreDisplay.textContent) }, crossword);
  setFeedback('Answer cleared. Try again slowly.');
}

async function submitWordAnswer() {
  const userAnswer = selectedLetters.map(item => item.letter).join('');

  if (userAnswer.length < activeScenario.answer_length) {
    setFeedback('Please complete all answer boxes before submitting.', 'wrong');
    return;
  }

  const data = await apiPost('api/submit_answer.php', {
    scenario_id: activeScenario.scenario_id,
    answer: userAnswer,
  });

  handleSubmitResult(data);
}


function renderCrosswordMode(data) {
  const scenario = data.scenario;
  const answerLength = Number(scenario.answer_length);
  const gridSize = Math.max(7, answerLength + 2);
  const middleRow = Math.floor(gridSize / 2);
  const startCol = Math.floor((gridSize - answerLength) / 2);
  const clueList = scenario.clues.map((clue, index) => `
    <li>
      <strong>Clue ${index + 1}: ${htmlEscape(clue.clue_title)}</strong><br>
      <span>${htmlEscape(clue.clue_text)}</span>
    </li>
  `).join('');

  let boardCells = '';
  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      const answerIndex = col - startCol;
      const isAnswerCell = row === middleRow && answerIndex >= 0 && answerIndex < answerLength;

      if (isAnswerCell) {
        const value = crosswordLetters[answerIndex] || '';
        const numberTag = answerIndex === 0 ? '<span class="crossword-number">1</span>' : '';
        boardCells += `
          <div class="crossword-square active-square">
            ${numberTag}
            <input
              class="crossword-input"
              data-pos="${answerIndex}"
              maxlength="1"
              value="${htmlEscape(value)}"
              aria-label="Crossword letter ${answerIndex + 1}"
              ${answered ? 'disabled' : ''}
            >
          </div>
        `;
      } else {
        boardCells += '<div class="crossword-square blocked-square" aria-hidden="true"></div>';
      }
    }
  }

  gameRoot.innerHTML = `
    <section class="game-panel level-info">
      <div>
        <h2>${htmlEscape(scenario.title)}</h2>
        <p>${htmlEscape(scenario.prompt)}</p>
      </div>
      <div class="badge">${htmlEscape(scenario.mode_name)}</div>
    </section>
    ${progressMarkup(data.progress)}
    <section class="game-panel crossword-layout">
      <div class="crossword-board-wrap">
        <h3>Crossword Board</h3>
        <p class="crossword-note">Type one letter per box. Use the clues to complete Across 1.</p>
        <div class="real-crossword-board" style="--crossword-size:${gridSize}">
          ${boardCells}
        </div>
      </div>
      <aside class="crossword-clues">
        <h3>Across 1</h3>
        <p><strong>Answer length:</strong> ${answerLength} letters</p>
        <ol>${clueList}</ol>
      </aside>
    </section>
    <section class="game-panel answer-area">
      <div class="controls">
        <button class="btn btn-green" id="submitCrossword">Submit</button>
        <button class="btn btn-gray" id="clearCrossword">Clear</button>
        <button class="btn btn-gold" id="hintCrossword">Hint</button>
        <button class="btn btn-primary" id="nextCrossword">Next</button>
      </div>
      <div class="feedback" id="feedback">Fill in the crossword boxes. Take your time.</div>
    </section>
  `;

  document.querySelectorAll('.crossword-input').forEach(input => {
    input.addEventListener('input', event => {
      const clean = event.target.value.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 1);
      const pos = Number(event.target.dataset.pos);
      event.target.value = clean;
      crosswordLetters[pos] = clean;

      if (clean && pos < answerLength - 1) {
        const nextInput = document.querySelector(`.crossword-input[data-pos="${pos + 1}"]`);
        if (nextInput) nextInput.focus();
      }
    });

    input.addEventListener('keydown', event => {
      const pos = Number(event.target.dataset.pos);
      if (event.key === 'Backspace' && !event.target.value && pos > 0) {
        const previousInput = document.querySelector(`.crossword-input[data-pos="${pos - 1}"]`);
        if (previousInput) previousInput.focus();
      }
    });
  });

  const firstInput = document.querySelector('.crossword-input[data-pos="0"]');
  if (firstInput && !answered) firstInput.focus();

  document.getElementById('submitCrossword').addEventListener('click', submitCrosswordAnswer);
  document.getElementById('clearCrossword').addEventListener('click', clearCrossword);
  document.getElementById('hintCrossword').addEventListener('click', () => setFeedback(`Hint: ${htmlEscape(activeScenario.hint)}`));
  document.getElementById('nextCrossword').addEventListener('click', loadScenario);
}

function clearCrossword() {
  if (answered) return;
  crosswordLetters = [];
  document.querySelectorAll('.crossword-input').forEach(input => input.value = '');
  const firstInput = document.querySelector('.crossword-input[data-pos="0"]');
  if (firstInput) firstInput.focus();
  setFeedback('Crossword cleared. Try again slowly.');
}

async function submitCrosswordAnswer() {
  const inputs = Array.from(document.querySelectorAll('.crossword-input'));
  const userAnswer = inputs.map(input => input.value.trim().toUpperCase()).join('');

  if (userAnswer.length < activeScenario.answer_length || inputs.some(input => input.value.trim() === '')) {
    setFeedback('Please fill every crossword box before submitting.', 'wrong');
    return;
  }

  const data = await apiPost('api/submit_answer.php', {
    scenario_id: activeScenario.scenario_id,
    answer: userAnswer,
  });

  handleSubmitResult(data);

  if (data.success && data.correct) {
    document.querySelectorAll('.crossword-input').forEach(input => input.disabled = true);
    document.querySelectorAll('.active-square').forEach(cell => cell.classList.add('correct-square'));
  }
}

function renderTileMode(data) {
  const scenario = data.scenario;
  gameRoot.innerHTML = `
    <section class="game-panel level-info">
      <div>
        <h2>${htmlEscape(scenario.title)}</h2>
        <p>${htmlEscape(scenario.prompt)}</p>
      </div>
      <div class="badge">${htmlEscape(scenario.mode_name)}</div>
    </section>
    ${progressMarkup(data.progress)}
    ${renderClues(scenario.clues)}
    <section class="game-panel answer-area">
      <div class="tile-options">
        ${scenario.options.map(option => `<button class="tile-btn" data-option="${htmlEscape(option)}">${htmlEscape(option)}</button>`).join('')}
      </div>
      <div class="controls">
        <button class="btn btn-green" id="submitTile">Submit</button>
        <button class="btn btn-gold" id="hintTile">Hint</button>
        <button class="btn btn-primary" id="nextTile">Next</button>
      </div>
      <div class="feedback" id="feedback">Select the safest or most correct solution tile.</div>
    </section>
  `;

  document.querySelectorAll('.tile-btn').forEach(button => {
    button.addEventListener('click', () => {
      if (answered) return;
      selectedTile = button.dataset.option;
      document.querySelectorAll('.tile-btn').forEach(btn => btn.classList.remove('selected-choice'));
      button.classList.add('selected-choice');
    });
  });

  document.getElementById('submitTile').addEventListener('click', submitTileAnswer);
  document.getElementById('hintTile').addEventListener('click', () => setFeedback(`Hint: ${htmlEscape(activeScenario.hint)}`));
  document.getElementById('nextTile').addEventListener('click', loadScenario);
}

async function submitTileAnswer() {
  if (!selectedTile) {
    setFeedback('Please select one tile first.', 'wrong');
    return;
  }

  const data = await apiPost('api/submit_answer.php', {
    scenario_id: activeScenario.scenario_id,
    answer: selectedTile,
  });

  if (data.success) {
    document.querySelectorAll('.tile-btn').forEach(btn => {
      if (btn.dataset.option === data.correct_answer) btn.classList.add('correct-choice');
      if (btn.dataset.option === selectedTile && !data.correct) btn.classList.add('wrong-choice');
      btn.disabled = true;
    });
  }

  handleSubmitResult(data);
}

function handleSubmitResult(data) {
  if (!data.success) {
    setFeedback(htmlEscape(data.message || 'Could not submit answer.'), 'wrong');
    return;
  }

  updateScore(data.total_score);
  answered = data.correct;

  if (data.correct) {
    let rewardText = '';
    if (data.new_rewards && data.new_rewards.length > 0) {
      rewardText = `<br><br><strong>New reward unlocked:</strong> ${data.new_rewards.map(htmlEscape).join(', ')}`;
    }
    setFeedback(`<strong>Correct!</strong> The answer is ${htmlEscape(data.correct_answer)}.<br>${htmlEscape(data.learning_tip)}${rewardText}`, 'correct');
  } else {
    setFeedback('Not yet. Review the clues and try again. Learning is allowed to be slow and safe.', 'wrong');
  }
}

if (modeKey) loadScenario();
