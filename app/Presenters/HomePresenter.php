<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Json;
use Nette\SmartObject;
use Nette\Application\UI;
use Nette\Utils\Validators;
use Nette\Diagnostics\Debugger;
use Nette\Utils\DateTime;
use Sunra\PhpSimple\HtmlDomParser;
use Nette\Utils\FileSystem;
use Nette\Security\Passwords;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Security\IAuthenticator;
use App\Model\UserManager;
use \stdClass;


final class HomePresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Context $database;
    private $httpRequest;

    private $sender = "Informace <info@info.cz>";

    public $SMTP_SERVER = 'smtp.forpsi.com';

    public $SMTP_EMAIL = 'info@info.cz';

    public $SMTP_PASSWORD = '.-..-';



    function __construct(Nette\Database\Context $database, Nette\Http\Request $httpRequest) {

        $this->database = $database;
        $this->httpRequest = $httpRequest;

    }

    function renderDefault(){

    }

    protected function createComponentAddContactForm(): UI\Form
    {
        $form = new UI\Form;

        $form->addText('name', 'Zadejte jméno:')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('id', 'pasteNameForm')
            ->setRequired('Zadejte jméno.');

        $form->addText('email', 'Zadejte email:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte email.');

        $form->addTextArea('message', 'Zadejte zprávu:')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Zadejte zprávu.');

        $form->addSubmit('send', 'Odeslat')
            ->setHtmlAttribute('class', 'btn btn-outline-success');

        $form->onSuccess[] = [$this, 'addContactSucceeded'];
        return $form;
    }

    public function addContactSucceeded(UI\Form $form, \stdClass $values): void
    {
        $mail = new Message;
        $mail->setFrom($this->sender)
            ->addTo('petr.plachy@cefip.cz')
            ->setSubject('Byl jste kontaktován')
            ->setHTMLBody("<b>Formulář vyplněn</b>, pan/paní - ".$values->name."<br/> s emailem - <a href='".$values->email."'>".$values->email."</a><br/> Napsal zprávu:<br/>".$values->message);
        

        $mailer = new Nette\Mail\SmtpMailer(
            host: $this->SMTP_SERVER,
            username: $this->SMTP_EMAIL,
            password: $this->SMTP_PASSWORD,
            encryption: 'ssl'
        );
        
        $mailer->send($mail);
        $this->flashMessage('Úspěšně kontaktováno!', 'success');
    }
}
