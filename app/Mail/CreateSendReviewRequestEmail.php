<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;
use Log;

class CreateSendReviewRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $email;
    protected $varificationCode;
    protected $businessId;
    protected $reviewId;
    protected $firstName;
    protected $emailMessage;
    protected $formatedBusinessName;
    protected $BusinessName;
    protected $redirectLink;
    protected $logo_image_src;
    protected $background_image_src;
    protected $top_background_color;
    protected $review_number_color;
    protected $star_rating_color;
    protected $email_subject;
    protected $email_heading;
    protected $email_message;
    protected $positive_answer;
    protected $negative_answer;
    protected $personal_avatar_src;
    protected $full_name;
    protected $company_role;
    protected $email_negative_answer_setup_heading;
    protected $email_negative_answer_setup_message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $firstName,
        $varificationCode,
        $email,
        $formatedBusinessName,
        $reviewId,
        $BusinessName,
        $userEmail,
        $emailMessage,
        $businessId,
        $logo_image_src,
        $background_image_src,
        $top_background_color,
        $review_number_color,
        $star_rating_color,
        $email_subject,
        $email_heading,
        $email_message,
        $positive_answer,
        $negative_answer,
        $personal_avatar_src,
        $full_name,
        $company_role,
        $email_negative_answer_setup_heading,
        $email_negative_answer_setup_message
    )
    {
        $url = URL('/');
        $this->firstName = $firstName;
        $this->BusinessName = $BusinessName;
        $this->emailMessage = $emailMessage;
        $this->formatedBusinessName = $formatedBusinessName;

        $this->logo_image_src = $logo_image_src;
        $this->background_image_src = $background_image_src;
        $this->top_background_color = $top_background_color;
        $this->review_number_color = $review_number_color;
        $this->star_rating_color = $star_rating_color;
        $this->email_subject = $email_subject;
        $this->email_heading = $email_heading;
        $this->email_message = $email_message;
        $this->positive_answer = $positive_answer;
        $this->negative_answer = $negative_answer;
        $this->personal_avatar_src = $personal_avatar_src;
        $this->full_name = $full_name;
        $this->company_role = $company_role;
        $this->email_negative_answer_setup_heading = $email_negative_answer_setup_heading;
        $this->email_negative_answer_setup_message = $email_negative_answer_setup_message;

        $this->email = $email;
        $this->varificationCode = $varificationCode;
        $this->businessId = $businessId;
        $this->reviewId = $reviewId;

        $url .= '/business-review/' . $email . '/' . $varificationCode . '/' . $businessId . '/' . $reviewId;

        $this->redirectLink = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info($this->firstName);
        Log::info($this->email_subject);
        Log::info('i am here');
        Log::info($this->full_name);
        Log::info('i am here 2');
        Log::info($this->positive_answer);
        return $this->view('email.customizeableemail')
            ->subject('Your Experience with ' . $this->BusinessName)
            ->from('no-reply@' . $this->formatedBusinessName . '.com')
            ->with([
                'email' => $this->email,
                'varificationCode' => $this->varificationCode,
                'businessId' => $this->businessId,
                'reviewId' =>$this->reviewId,

                'firstName' => $this->firstName,
                'BusinessName' => $this->BusinessName,
                'redirectLink' => $this->redirectLink,
                'emailMessage' => $this->emailMessage,

                'logo_image_src' => $this->logo_image_src,
                'background_image_src' => $this->background_image_src,
                'top_background_color' => $this->top_background_color,
                'review_number_color' => $this->review_number_color,
                'star_rating_color' => $this->star_rating_color,
                'email_subject' => $this->email_subject,
                'email_heading' => $this->email_heading,
                'email_message'=> $this->email_message,
                'positive_answer' => $this->positive_answer,
                'negative_answer' => $this->negative_answer,
                'personal_avatar_src' => $this->personal_avatar_src,
                'full_name' => $this->full_name,
                'company_role' => $this->company_role,
                'email_negative_answer_setup_heading' => $this->email_negative_answer_setup_heading,
                'email_negative_answer_setup_message' => $this->email_negative_answer_setup_message
            ]);
    }
}
