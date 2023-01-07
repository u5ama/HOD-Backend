<p style="color: grey;">Do not reply to this email. Click on link below to leave your review.</p>
@if(empty($emailMessage))
    <p>Hi {{ $firstName }},</p>
    <p> Thanks for choosing {{ $BusinessName }}. If you have a few minutes, I'd like to invite you to tell us about your experience. Your feedback is very important to us and it would be awesome if you can share it with us and our potential customers.
    </p></br>
@else
    <p style="white-space: pre-line;word-wrap: break-word;"> <?php echo $emailMessage; ?>
    </p></br>
@endif
<a href="<?php echo $redirectLink; ?>">Add a Quick Review</a>
