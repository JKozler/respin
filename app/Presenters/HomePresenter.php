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
}
