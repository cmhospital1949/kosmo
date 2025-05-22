<?php
// This component renders a newsletter signup form
// No function declaration at the top to avoid conflicts
// The component expects two variables to be set before including it:
// 1. $language - The current language (en or ko)
// 2. $trans - The translations array for the newsletter form
?>

<div class="w-full bg-blue-50 p-6 rounded-lg shadow-md newsletter-form">
    <h3 class="text-xl font-semibold mb-2"><?php echo $trans['newsletter_heading']; ?></h3>
    <p class="text-gray-600 mb-4"><?php echo $trans['newsletter_subheading']; ?></p>
    
    <form id="newsletterForm" class="space-y-4">
        <div>
            <label for="newsletter_name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $trans['name_label']; ?></label>
            <input type="text" id="newsletter_name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
        
        <div>
            <label for="newsletter_email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $trans['email_label']; ?> *</label>
            <input type="email" id="newsletter_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
        
        <div>
            <label for="newsletter_language" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $trans['language_label']; ?></label>
            <select id="newsletter_language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="en" <?php echo isset($language) && $language === 'en' ? 'selected' : ''; ?>><?php echo $trans['english']; ?></option>
                <option value="ko" <?php echo isset($language) && $language === 'ko' ? 'selected' : ''; ?>><?php echo $trans['korean']; ?></option>
            </select>
        </div>
        
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
            <?php echo $trans['subscribe_button']; ?>
        </button>
    </form>
    
    <div id="newsletterMessage" class="mt-4 p-3 rounded-md hidden"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newsletterForm');
    const messageDiv = document.getElementById('newsletterMessage');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(form);
        
        // Simple validation
        const email = formData.get('email');
        if (!email || !email.includes('@')) {
            messageDiv.textContent = "<?php echo $trans['email_required']; ?>";
            messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800');
            messageDiv.classList.add('bg-red-100', 'text-red-800');
            return;
        }
        
        // Submit form via AJAX
        fetch('newsletter-subscribe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Display result message
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            
            if (data.success) {
                messageDiv.classList.remove('bg-red-100', 'text-red-800');
                messageDiv.classList.add('bg-green-100', 'text-green-800');
                form.reset();
            } else {
                messageDiv.classList.remove('bg-green-100', 'text-green-800');
                messageDiv.classList.add('bg-red-100', 'text-red-800');
            }
        })
        .catch(error => {
            // Display error message
            messageDiv.textContent = "<?php echo $trans['error_message']; ?>";
            messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800');
            messageDiv.classList.add('bg-red-100', 'text-red-800');
            console.error('Error:', error);
        });
    });
});
</script>