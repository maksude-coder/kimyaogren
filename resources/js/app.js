/**
 * Landing-page sample question: pick an option, check it, show the
 * step-by-step solution and — for a wrong pick — why that answer came out.
 */
function initSampleQuestion(root) {
    const options = [...root.querySelectorAll('[data-option]')];
    const checkButton = root.querySelector('[data-check]');
    const result = root.querySelector('[data-result-panel]');
    const status = root.querySelector('[data-status]');
    const hint = root.querySelector('[data-hint-panel]');
    const correct = root.dataset.correct;
    let picked = null;

    const select = (option) => {
        picked = option.dataset.option;
        options.forEach((o) => o.setAttribute('aria-checked', String(o === option)));
        checkButton.disabled = false;
    };

    options.forEach((option) => {
        option.addEventListener('click', () => select(option));
    });

    root.querySelector('[role="radiogroup"]').addEventListener('keydown', (event) => {
        const step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[event.key];

        if (step === undefined || options[0].disabled) {
            return;
        }

        event.preventDefault();
        const index = options.findIndex((o) => o.dataset.option === picked);
        const next = options[(index + step + options.length) % options.length];
        select(next);
        next.focus();
    });

    checkButton.addEventListener('click', () => {
        if (!picked) {
            return;
        }

        const right = picked === correct;
        const correctText = root.querySelector(`[data-option="${correct}"] [data-text]`).textContent;

        options.forEach((o) => {
            o.disabled = true;

            if (o.dataset.option === correct) {
                o.dataset.result = 'correct';
                o.querySelector('.option-key').textContent = '✓';
                o.querySelector('[data-tag]').textContent = 'Doğru cevap';
            } else if (o.dataset.option === picked) {
                o.dataset.result = 'wrong';
                o.querySelector('.option-key').textContent = '✕';
                o.querySelector('[data-tag]').textContent = 'Senin cevabın';
            }
        });

        status.dataset.state = right ? 'correct' : 'wrong';
        status.querySelector('[data-status-icon]').textContent = right ? '✓' : '✕';
        status.querySelector('[data-status-text]').textContent = right
            ? 'Doğru. Mol oranını doğru okudun.'
            : `Yanlış. Doğru cevap: ${correct} · ${correctText}`;

        const hintText = right ? '' : root.querySelector(`[data-option="${picked}"]`).dataset.hint;
        hint.hidden = !hintText;
        hint.querySelector('[data-hint-text]').textContent = hintText;

        checkButton.hidden = true;
        result.hidden = false;
        status.focus();
    });

    root.querySelector('[data-reset]').addEventListener('click', () => {
        picked = null;
        options.forEach((o) => {
            o.disabled = false;
            o.setAttribute('aria-checked', 'false');
            delete o.dataset.result;
            o.querySelector('.option-key').textContent = o.dataset.option;
            o.querySelector('[data-tag]').textContent = '';
        });
        checkButton.disabled = true;
        checkButton.hidden = false;
        result.hidden = true;
        options[0].focus();
    });
}

document.querySelectorAll('[data-sample-question]').forEach(initSampleQuestion);

/**
 * Password field (§ 08): "Göster / Gizle" toggle and the rule list that is
 * ticked while typing, so submitting holds no surprise.
 */
function initPasswordField(field) {
    const input = field.querySelector('input');
    const toggle = field.querySelector('[data-password-toggle]');
    const rules = document.querySelector(`[data-password-rules="${input.id}"]`);

    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Gizle' : 'Göster';
        toggle.setAttribute('aria-pressed', String(show));
    });

    if (!rules) {
        return;
    }

    const checks = { length: (value) => value.length >= 8, digit: (value) => /\d/.test(value) };
    const update = () => {
        rules.querySelectorAll('[data-rule]').forEach((rule) => {
            const met = checks[rule.dataset.rule](input.value);
            rule.dataset.met = String(met);
            rule.querySelector('[data-rule-icon]').textContent = met ? '✓' : '○';
        });
    };

    input.addEventListener('input', update);
    update();
}

document.querySelectorAll('[data-password-field]').forEach(initPasswordField);
