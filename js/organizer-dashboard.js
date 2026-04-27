const form       = document.getElementById('eventsForm');
const formAction = document.getElementById('formAction');
const editBtn    = document.getElementById('editBtn');
const deleteBtn  = document.getElementById('deleteBtn');
const saveBtn    = document.getElementById('saveBtn');
const checkboxes = document.querySelectorAll('.row-check');

let editingRow = null;

function selectedCheckboxes() {
  return [...checkboxes].filter(c => c.checked);
}

function refreshButtons() {
  if (editingRow) return;
  const count = selectedCheckboxes().length;
  editBtn.disabled   = count !== 1;
  deleteBtn.disabled = count === 0;
  saveBtn.disabled   = true;
}

function startEditing(row) {
  editingRow = row;
  const dt   = row.querySelector('.cell-datetime');
  const game = row.querySelector('.cell-game');

  const dtValue = dt.dataset.value.replace(' ', 'T').slice(0, 16);
  dt.innerHTML = `<input type="datetime-local" name="date_time" value="${dtValue}" required>`;

  const gameId = game.dataset.value;
  let options = '';
  for (const g of GAMES) {
    options += `<option value="${g.id}"${String(g.id) === gameId ? ' selected' : ''}>${g.name}</option>`;
  }
  game.innerHTML = `<select name="game_id" required>${options}</select>`;

  const hidden = document.createElement('input');
  hidden.type  = 'hidden';
  hidden.name  = 'event_id';
  hidden.value = row.dataset.id;
  form.appendChild(hidden);

  editBtn.disabled   = true;
  deleteBtn.disabled = true;
  saveBtn.disabled   = false;
  checkboxes.forEach(c => c.disabled = true);
}

editBtn.addEventListener('click', () => {
  const checked = selectedCheckboxes();
  if (checked.length !== 1) return;
  const row = checked[0].closest('tr');
  startEditing(row);
});

saveBtn.addEventListener('click', () => {
  formAction.value = 'update';
  form.submit();
});

deleteBtn.addEventListener('click', () => {
  if (!confirm('Delete the selected event(s)?')) return;
  formAction.value = 'delete';
  form.submit();
});

checkboxes.forEach(c => c.addEventListener('change', refreshButtons));
