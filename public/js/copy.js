/**
 * Copy to Clipboard functionality
 * Supports both modern Clipboard API and fallback for older browsers
 */

function copyToClipboard(elementId, label) {
    const element = document.getElementById(elementId);
    const text = element.querySelector('span') ? element.querySelector('span').textContent : element.textContent;

    // Try to use the modern clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showCopySuccess(label);
        }).catch(() => {
            fallbackCopyTextToClipboard(text, label);
        });
    } else {
        fallbackCopyTextToClipboard(text, label);
    }
}

function fallbackCopyTextToClipboard(text, label) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(label);
        } else {
            showCopyError(label);
        }
    } catch (err) {
        showCopyError(label);
    }

    document.body.removeChild(textArea);
}

function showCopySuccess(label) {
    // Create a temporary success message
    const message = document.createElement('div');
    message.className = 'copy-notification success';
    message.innerHTML = `<i class="fas fa-check"></i> ${label} copied to clipboard!`;
    document.body.appendChild(message);

    // Remove after 3 seconds
    setTimeout(() => {
        if (message.parentNode) {
            message.parentNode.removeChild(message);
        }
    }, 3000);
}

function showCopyError(label) {
    // Create a temporary error message
    const message = document.createElement('div');
    message.className = 'copy-notification error';
    message.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Failed to copy ${label}`;
    document.body.appendChild(message);

    // Remove after 3 seconds
    setTimeout(() => {
        if (message.parentNode) {
            message.parentNode.removeChild(message);
        }
    }, 3000);
}

// Add notification styles dynamically
const notificationStyles = `
    .copy-notification {
        position: fixed;
        top: 1rem;
        right: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        color: white;
        font-weight: 500;
        z-index: 50;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .copy-notification.success {
        background-color: #10b981;
    }

    .copy-notification.error {
        background-color: #ef4444;
    }

    .copy-notification i {
        margin-right: 0.5rem;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;

// Inject styles if not already present
if (!document.getElementById('copy-notification-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'copy-notification-styles';
    styleSheet.textContent = notificationStyles;
    document.head.appendChild(styleSheet);
}
