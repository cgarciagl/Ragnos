<div class="divfield col-sm-12 mb-3">
    <div class="form-floating" id='group_<?= $name ?>'>
        <input type="text" class="form-control" id="<?= $name ?>_display"
            placeholder="<?= esc($placeholder ?? $label) ?>" <?= $extra_attributes ?>>
        <input type="hidden" id="<?= $name ?>" name="<?= $name ?>" value="<?= esc($value) ?>">
        <label for="<?= $name ?>_display"><?= esc($label) ?></label>
        <div id="<?= $name ?>_pills" class="mt-2 text-start">
            <!-- Pills will be inserted here -->
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const displayInput = document.getElementById('<?= $name ?>_display');
        const hiddenInput = document.getElementById('<?= $name ?>');
        const pillsContainer = document.getElementById('<?= $name ?>_pills');

        let tags = [];
        try {
            const rawValue = hiddenInput.value;
            if (rawValue) {
                tags = JSON.parse(rawValue);
                if (!Array.isArray(tags)) {
                    // Try to handle existing comma-separated strings if any
                    tags = rawValue.split(',').map(s => s.trim()).filter(s => s);
                }
            }
        } catch (e) {
            console.warn("Could not parse existing tags for <?= $name ?>", e);
            tags = [];
        }

        function updateHiddenInput() {
            hiddenInput.value = JSON.stringify(tags);
        }

        function createPill(text) {
            const pill = document.createElement('span');
            pill.className = 'badge rounded-pill bg-primary me-1 mb-1 fs-6';
            pill.style.cursor = 'pointer';
            pill.innerHTML = `
                ${text} <span class="ms-1" aria-hidden="true">&times;</span>
            `;
            pill.onclick = function () {
                tags = tags.filter(t => t !== text);
                renderPills();
                updateHiddenInput();
            };
            return pill;
        }

        function renderPills() {
            pillsContainer.innerHTML = '';
            tags.forEach(tag => {
                pillsContainer.appendChild(createPill(tag));
            });
        }

        displayInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = this.value.trim().replace(',', '');
                if (val && !tags.includes(val)) {
                    tags.push(val);
                    renderPills();
                    updateHiddenInput();
                    this.value = '';
                }
            } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
                // Optional: remove last tag on backspace if input is empty
                tags.pop();
                renderPills();
                updateHiddenInput();
            }
        });

        // Initialize display
        renderPills();
    });
</script>