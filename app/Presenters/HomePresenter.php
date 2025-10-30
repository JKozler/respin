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

    private $sender = "Obchod <obchod@respinteam.cz>";

    public $SMTP_SERVER = 'smtp.seznam.cz';

    public $SMTP_EMAIL = 'obchod@respinteam.cz';

    public $SMTP_PASSWORD = 'so14votpavel';



    function __construct(Nette\Database\Context $database, Nette\Http\Request $httpRequest) {

        $this->database = $database;
        $this->httpRequest = $httpRequest;

    }

    function renderDefault(){
        $this->template->categories = $this->database->query('
        SELECT * FROM gallery_categories 
        WHERE active = 1 
        ORDER BY sort_order ASC, name ASC
    ')->fetchAll();

    // Načtení všech aktivních obrázků s jejich kategoriemi
    $this->template->images = $this->database->query('
        SELECT gi.*, gc.name as category_name, gc.slug as category_slug 
        FROM gallery_images gi 
        JOIN gallery_categories gc ON gi.category_id = gc.id 
        WHERE gi.active = 1 AND gc.active = 1 
        ORDER BY gi.sort_order ASC, gi.created_at DESC
    ')->fetchAll();
    }

    function renderServices(){

    }

    function renderAbout(){

    }

    function renderSupport(){
        $this->template->faqs = $this->database->table('faq')->fetchAll();
        $this->template->troubleshooting = $this->database->table('troubleshooting')->fetchAll();
        $this->template->maintenance = $this->database->table('maintenance')->fetchAll();
    }

    function renderContacts(){

    }

    function renderGallery(){
        $this->template->categories = $this->database->query('
            SELECT * FROM gallery_categories 
            WHERE active = 1 
            ORDER BY sort_order ASC, name ASC
        ')->fetchAll();

        // Načtení všech aktivních obrázků s jejich kategoriemi
        $this->template->images = $this->database->query('
            SELECT gi.*, gc.name as category_name, gc.slug as category_slug 
            FROM gallery_images gi 
            JOIN gallery_categories gc ON gi.category_id = gc.id 
            WHERE gi.active = 1 AND gc.active = 1 
            ORDER BY gi.sort_order ASC, gi.created_at DESC
        ')->fetchAll();
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
        $this->database->table('contacts')->insert([
            'name' => $values->name,
            'email' => $values->email,
            'message' => $values->message,
            'created_at' => new \DateTime(),
        ]);

        $mail = new Message;
        $mail->setFrom($this->sender)
            ->addTo('vyroba@respinteam.cz')
            ->setSubject('Byl jste kontaktován')
            ->setHTMLBody("<b>Formulář vyplněn</b>, pan/paní - ".$values->name."<br/> s emailem - <a href='".$values->email."'>".$values->email."</a><br/> Napsal zprávu:<br/>".$values->message);
        

            $mailer = new Nette\Mail\SmtpMailer(
                'smtp.seznam.cz',
                'obchod@respinteam.cz',
                'so14votpavel',
                587,
                'tls'
            );

        try {
            $mailer->send($mail);
            $this->flashMessage('Úspěšně kontaktováno!', 'success');
        } catch (\Throwable $e) {
            $this->flashMessage('Někde nastala chyba, prosím kontaktujte nás přes email napřímo...', 'success');
        }
    }
}