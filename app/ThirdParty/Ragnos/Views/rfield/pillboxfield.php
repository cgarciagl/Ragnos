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
        const rawValue = hiddenInput.value;

        // Logic to determine initial tags:
        // 1. Try to parse as JSON Array
        // 2. If fail, treat as simple comma-separated string
        try {
            const parsed = JSON.parse(rawValue);
            if (Array.isArray(parsed)) {
                tags = parsed;
            } else {
                // Determine if it was a JSON string or number that is not an array
                // If so, we treat the raw string value as the input
                throw new Error("Parsed JSON is not an array");
            }
        } catch (e) {
            // Fallback for non-JSON content (e.g. "EMEA", "Tag1, Tag2")
            if (rawValue && rawValue.trim() !== '') {
                tags = rawValue.split(',').map(s => s.trim()).filter(s => s.length > 0);
            }
        }

        // Debug info
        // console.log('Pillbox [<?= $name ?>] init. Raw:', rawValue, 'Tags:', tags);

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

        function addTagsFromInput(inputValue) {
            if (!inputValue) return;

            // Split by comma or newline, trim, and filter empties
            const newTags = inputValue.split(/,|\n/).map(s => s.trim()).filter(s => s.length > 0);

            let changed = false;
            newTags.forEach(tag => {
                if (!tags.includes(tag)) {
                    tags.push(tag);
                    changed = true;
                }
            });

            if (changed) {
                renderPills();
                updateHiddenInput();
            }
        }

        displayInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTagsFromInput(this.value);
                this.value = '';
            } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
                tags.pop();
                renderPills();
                updateHiddenInput();
            }
        });

        displayInput.addEventListener('blur', function () {
            addTagsFromInput(this.value);
            this.value = '';
        });

        displayInput.addEventListener('paste', function (e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            addTagsFromInput(pastedData);
            this.value = '';
        });

        // Initialize display
        renderPills();
    });
</script>