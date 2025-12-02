const TWP = {
    /**
     * Switches between Preview and Source tabs.
     */
    switchTab: function(uid, tabName) {
        const wrapper = document.getElementById(uid);

        wrapper.querySelectorAll('.twp-tab-btn').forEach(btn => {
            if (btn.innerText.toLowerCase().includes(tabName)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        wrapper.querySelectorAll('.twp-pane').forEach(pane => {
            if (pane.dataset.pane === tabName) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });
    },

    /**
     * Copies code from the Source block to the clipboard.
     */
    copyCode: function(uid, btnElement) {
        const codeBlock = document.getElementById(uid + '-source');
        // Get text content, ensuring we strip HTML tags that Prism adds for coloring.
        const codeText = codeBlock.textContent || codeBlock.innerText;

        const originalHTML = btnElement.innerHTML;

        const successFeedback = () => {
            const span = btnElement.querySelector('.twp-copy-text');
            if (span) span.innerText = 'Copied!';
            setTimeout(() => {
                btnElement.innerHTML = originalHTML;
            }, 2000);
        };

        // Use Modern Clipboard API if available and context is secure (HTTPS/Localhost)
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(codeText).then(successFeedback);
        } else {
            // Fallback for older browsers or non-secure contexts
            const textArea = document.createElement('textarea');
            textArea.value = codeText;
            textArea.style.position = 'fixed'; // Avoid scrolling to bottom
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                successFeedback();
            } catch (err) {
                console.error('Failed to copy', err);
            }
            document.body.removeChild(textArea);
        }
    }
};
