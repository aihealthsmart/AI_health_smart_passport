<style>
    /* Footer Container */
    .site-footer {
        background-color: #f1f3f4; /* Light gray background like the screenshot */
        padding: 20px 0;
        border-top: 1px solid #dadce0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        width: 100%;
        margin-top: auto; /* Pushes footer to bottom if using a flex wrapper */
    }

    /* Flexbox Layout for Links */
    .footer-content {
        display: flex;
        justify-content: center; /* Centers the items */
        align-items: center;
        flex-wrap: wrap; /* Allows wrapping on small screens */
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Individual Link Styling */
    .footer-link {
        color: #5f6368; /* Subtle gray text */
        text-decoration: none;
        font-size: 13px;
        margin: 5px 15px;
        transition: color 0.2s;
    }

    .footer-link:hover {
        text-decoration: underline;
        color: #202124; /* Darker on hover */
    }

    /* Copyright Text */
    .footer-copy {
        color: #70757a;
        font-size: 13px;
        margin-left: 20px;
    }
</style>

<footer class="site-footer">
    <div class="footer-content">
        <a href="feedback.php" class="footer-link">Feedback</a>
        <a href="namca.php" class="footer-link">namca</a>
        <a href="hipaa.php" class="footer-link">hipaa</a>
        <a href="confidentiality.php" class="footer-link">confidentiality</a>
        <a href="privacy.php" class="footer-link">Privacy</a>
        
        <span class="footer-copy">
            &copy; <?php echo date("Y"); ?> Microsoft-Style Project
        </span>
    </div>
</footer>