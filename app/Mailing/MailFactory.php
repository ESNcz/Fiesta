<?php

namespace App\Mailing;

use App\Model\TokenRepository;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use SendGrid;
use SendGrid\Mail\From;
use SendGrid\Mail\Mail;

/**
 * Class MailFactory
 * @package App\Mailing
 */
class MailFactory extends Presenter
{
    private $engine;

    /**
     * MailFactory constructor.
     * @param ILatteFactory $engine
     */
    public function __construct(ILatteFactory $engine)
    {
        $this->engine = $engine;
    }

    private function sendEmail($message)
    {
        $sendGrid = new SendGrid($this->context->parameters['sendGridApiKey']);
        $response = $sendGrid->send($message);

        bdump($response->statusCode(), "Status code");
        bdump($response->headers(), "Header");
        bdump($response->body(), "Body");
    }

    /**
     * Send message that profile is activated
     * @param $email
     */
    public function profileActivated($email)
    {
        $message = new Mail(new From("noreply@fiesta.esncz.org", "Fiesta"));
        $message->addTo($email);
        $message->setSubject("Fiesta - Account Confirmed!");
        $message->addContent("text/html", $this->engine->create()->renderToString(__DIR__ . '/templates/status.activate.latte', []));

        $this->sendEmail($message);
    }

    public function profilePending($email)
    {
        $message = new Mail(new From("noreply@fiesta.esncz.org", "Fiesta"));
        $message->addTo($email);
        $message->setSubject("Fiesta - Account Pending!");
        $message->addContent("text/html", $this->engine->create()->renderToString(__DIR__ . '/templates/status.pending.latte', []));

        $this->sendEmail($message);
    }

    public function profileBanned($email)
    {
        $message = new Mail(new From("noreply@fiesta.esncz.org", "Fiesta"));
        $message->addTo($email);
        $message->setSubject("Fiesta - Account Pending!");
        $message->addContent("text/html", $this->engine->create()->renderToString(__DIR__ . '/templates/status.banned.latte', []));

        $this->sendEmail($message);
    }

    public function buddyMatch($member, $international)
    {
//        $message = new Mail(new From("noreply@fiesta.esncz.org", "Fiesta"));
//
//        $message->addTo($international);
//        $message->setSubject("Fiesta - It's a match!");
//        $message->addContent("text/html", $this->engine->create()->renderToString(__DIR__ . '/templates/buddyMatch.latte'));
//
//        $this->sendEmail($message);
    }

    public function PickupMatch($member, $international)
    {
//
//        $message = new Mail(new From("noreply@fiesta.esncz.org", "Fiesta"));
//        $message->addTo($international);
//        $message->setSubject("Fiesta - It's a match!");
//        $message->addContent("text/html", $this->engine->create()->renderToString(__DIR__ . '/templates/PickupMatch.latte', $params));
//
//        $this->sendEmail($message);
    }

    public function resetPassword($values)
    {
        $token = new TokenRepository();
        $token->createToken(["email" => $values]);
        $params["token"] = $token->getJWTToken();

        $message = new Mail(new From("noreply@fiesta.esncz.org", "Fiesta"));
        $message->addTo($values);
        $message->setSubject("Reset Your Fiesta Password");
        $message->addContent("text/html", $this->engine->create()->renderToString(__DIR__ . '/templates/resetPassword.latte', $params));

        $this->sendEmail($message);
    }

}
