<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Nette\Http\FileUpload;

final class AdminPresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Context $database;
    private string $uploadDir;

    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
        $this->setLayout('layoutAdmin');
        $this->uploadDir = __DIR__ . '/../../www/uploads/gallery/';
        
        // Vytvoření upload složky pokud neexistuje
        if (!is_dir($this->uploadDir)) {
            FileSystem::createDir($this->uploadDir);
        }
    }

    protected function startup()
    {
        parent::startup();
        
        // Základní ochrana - můžete nahradit vlastní autentifikací
        $session = $this->getSession('admin');
        if (!$session->logged && $this->action !== 'login') {
            $this->redirect('Admin:login');
        }
    }

    // LOGIN FUNKCE
    public function renderLogin()
    {
        $this->setLayout('layoutLogin');
    }

    protected function createComponentLoginForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Zadejte uživatelské jméno');
        
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadejte heslo');
        
        $form->addSubmit('login', 'Přihlásit');
        
        $form->onSuccess[] = [$this, 'loginSucceeded'];
        return $form;
    }

    public function loginSucceeded(Form $form, \stdClass $values)
    {
        // Jednoduché přihlášení - nahraďte vlastní logikou
        if ($values->username === 'admin' && $values->password === 'heslo123') {
            $session = $this->getSession('admin');
            $session->logged = true;
            $session->user = $values->username;
            $this->redirect('Admin:default');
        } else {
            $this->flashMessage('Nesprávné přihlašovací údaje', 'error');
        }
    }

    public function actionLogout()
    {
        $session = $this->getSession('admin');
        $session->remove();
        $this->redirect('Admin:login');
    }

    // DASHBOARD
    public function renderDefault()
    {
        $categoriesCount = $this->database->query('SELECT COUNT(*) as count FROM gallery_categories WHERE active = ?', 1)
            ->fetch()->count;
        
        $imagesCount = $this->database->query('SELECT COUNT(*) as count FROM gallery_images WHERE active = ?', 1)
            ->fetch()->count;
        
        $faqCount = $this->database->table('faq')->count();
        $troubleshootingCount = $this->database->table('troubleshooting')->count();
        $maintenanceCount = $this->database->table('maintenance')->count();
        
        $this->template->categoriesCount = $categoriesCount;
        $this->template->imagesCount = $imagesCount;
        $this->template->faqCount = $faqCount;
        $this->template->troubleshootingCount = $troubleshootingCount;
        $this->template->maintenanceCount = $maintenanceCount;
    }

    // KATEGORIE
    public function renderCategories()
    {
        $categories = $this->database->query('
            SELECT c.*, COUNT(i.id) as images_count 
            FROM gallery_categories c 
            LEFT JOIN gallery_images i ON c.id = i.category_id 
            GROUP BY c.id 
            ORDER BY c.sort_order ASC, c.name ASC
        ')->fetchAll();
        $this->template->categories = $categories;
    }

    public function renderAddCategory()
    {
    }

    public function renderEditCategory(int $id)
    {
        $category = $this->database->query('SELECT * FROM gallery_categories WHERE id = ?', $id)->fetch();
        if (!$category) {
            $this->error('Kategorie nebyla nalezena');
        }
        $this->template->category = $category;
        
        // Nastavení hodnot formuláře
        $form = $this->getComponent('categoryForm');
        $form->setDefaults([
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'active' => (bool)$category->active
        ]);
    }

    protected function createComponentCategoryForm(): Form
    {
        $form = new Form;
        
        $form->addText('name', 'Název kategorie:')
            ->setRequired('Zadejte název kategorie');
        
        $form->addText('slug', 'URL slug:')
            ->setRequired('Zadejte URL slug');
        
        $form->addTextArea('description', 'Popis:')
            ->setHtmlAttribute('rows', 3);
        
        $form->addInteger('sort_order', 'Pořadí:')
            ->setDefaultValue(0);
        
        $form->addCheckbox('active', 'Aktivní')
            ->setDefaultValue(true);
        
        $form->addSubmit('save', 'Uložit');
        
        $form->onSuccess[] = [$this, 'categoryFormSucceeded'];
        return $form;
    }

    // === FAQ ===

    public function renderFaq()
    {
        $this->template->faqs = $this->database->table('faq')
            ->order('id DESC')
            ->fetchAll();
    }

    public function renderAddFaq()
    {
        // prázdná šablona s formulářem
    }

    public function renderEditFaq(int $id)
    {
        $faq = $this->database->table('faq')->get($id);
        if (!$faq) {
            $this->error('FAQ položka nebyla nalezena');
        }
        $this->template->faq = $faq;

        $form = $this->getComponent('faqForm');
        $form->setDefaults([
            'question' => $faq->question,
            'answer' => $faq->answer,
        ]);
    }

    protected function createComponentFaqForm(): Form
    {
        $form = new Form;

        $form->addTextArea('question', 'Otázka:')
            ->setRequired('Zadejte otázku')
            ->setHtmlAttribute('rows', 2)
            ->setHtmlAttribute('class', 'form-control');

        $form->addTextArea('answer', 'Odpověď:')
            ->setRequired('Zadejte odpověď')
            ->setHtmlAttribute('rows', 6)
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'faqFormSucceeded'];
        return $form;
    }

    public function faqFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = $this->getParameter('id');

        if ($id) {
            // úprava
            $this->database->table('faq')->where('id', $id)->update([
                'question' => $values->question,
                'answer' => $values->answer,
            ]);
            $this->flashMessage('FAQ bylo upraveno', 'success');
        } else {
            // přidání
            $this->database->table('faq')->insert([
                'question' => $values->question,
                'answer' => $values->answer,
                'created_at' => new \DateTime(),
            ]);
            $this->flashMessage('FAQ bylo přidáno', 'success');
        }

        $this->redirect('Admin:faq');
    }

    public function renderDeleteFaq(int $id): void
    {
        $faq = $this->database->table('faq')->get($id);
        if ($faq) {
            $faq->delete();
            $this->flashMessage('FAQ bylo smazáno', 'success');
        }
        $this->redirect('Admin:faq');
    }

    // === TROUBLESHOOTING (Řešení potíží) ===

    public function renderTroubleshooting()
    {
        $this->template->troubleshooting = $this->database->table('troubleshooting')
            ->order('id DESC')
            ->fetchAll();
    }

    public function renderAddTroubleshooting()
    {
        // prázdná šablona s formulářem
    }

    public function renderEditTroubleshooting(int $id)
    {
        $item = $this->database->table('troubleshooting')->get($id);
        if (!$item) {
            $this->error('Položka nebyla nalezena');
        }
        $this->template->item = $item;

        $form = $this->getComponent('troubleshootingForm');
        $form->setDefaults([
            'icon' => $item->icon,
            'title' => $item->title,
            'causes' => $item->causes,
            'solution' => $item->solution,
        ]);
    }

    protected function createComponentTroubleshootingForm(): Form
    {
        $form = new Form;

        $form->addText('icon', 'Ikona (emoji):')
            ->setRequired('Zadejte ikonu')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', '⚠️');

        $form->addTextArea('title', 'Název problému:')
            ->setRequired('Zadejte název')
            ->setHtmlAttribute('rows', 2)
            ->setHtmlAttribute('class', 'form-control');

        $form->addTextArea('causes', 'Možné příčiny:')
            ->setRequired('Zadejte možné příčiny')
            ->setHtmlAttribute('rows', 4)
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', 'Popište jednotlivé možné příčiny oddělené odstavci');

        $form->addTextArea('solution', 'Doporučený postup:')
            ->setRequired('Zadejte řešení')
            ->setHtmlAttribute('rows', 4)
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'troubleshootingFormSucceeded'];
        return $form;
    }

    public function troubleshootingFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = $this->getParameter('id');

        if ($id) {
            // úprava
            $this->database->table('troubleshooting')->where('id', $id)->update([
                'icon' => $values->icon,
                'title' => $values->title,
                'causes' => $values->causes,
                'solution' => $values->solution,
            ]);
            $this->flashMessage('Položka byla upravena', 'success');
        } else {
            // přidání
            $this->database->table('troubleshooting')->insert([
                'icon' => $values->icon,
                'title' => $values->title,
                'causes' => $values->causes,
                'solution' => $values->solution,
                'created_at' => new \DateTime(),
            ]);
            $this->flashMessage('Položka byla přidána', 'success');
        }

        $this->redirect('Admin:troubleshooting');
    }

    public function renderDeleteTroubleshooting(int $id): void
    {
        $item = $this->database->table('troubleshooting')->get($id);
        if ($item) {
            $item->delete();
            $this->flashMessage('Položka byla smazána', 'success');
        }
        $this->redirect('Admin:troubleshooting');
    }

    // === MAINTENANCE (Tipy na údržbu) ===

    public function renderMaintenance()
    {
        $this->template->maintenance = $this->database->table('maintenance')
            ->order('id DESC')
            ->fetchAll();
    }

    public function renderAddMaintenance()
    {
        // prázdná šablona s formulářem
    }

    public function renderEditMaintenance(int $id)
    {
        $item = $this->database->table('maintenance')->get($id);
        if (!$item) {
            $this->error('Položka nebyla nalezena');
        }
        $this->template->item = $item;

        $form = $this->getComponent('maintenanceForm');
        $form->setDefaults([
            'icon' => $item->icon,
            'title' => $item->title,
            'description' => $item->description,
        ]);
    }

    protected function createComponentMaintenanceForm(): Form
    {
        $form = new Form;

        $form->addText('icon', 'Ikona (emoji):')
            ->setRequired('Zadejte ikonu')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('placeholder', '🛠️');

        $form->addTextArea('title', 'Název tipu:')
            ->setRequired('Zadejte název')
            ->setHtmlAttribute('rows', 2)
            ->setHtmlAttribute('class', 'form-control');

        $form->addTextArea('description', 'Popis:')
            ->setRequired('Zadejte popis')
            ->setHtmlAttribute('rows', 6)
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('save', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = [$this, 'maintenanceFormSucceeded'];
        return $form;
    }

    public function maintenanceFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = $this->getParameter('id');

        if ($id) {
            // úprava
            $this->database->table('maintenance')->where('id', $id)->update([
                'icon' => $values->icon,
                'title' => $values->title,
                'description' => $values->description,
            ]);
            $this->flashMessage('Tip byl upraven', 'success');
        } else {
            // přidání
            $this->database->table('maintenance')->insert([
                'icon' => $values->icon,
                'title' => $values->title,
                'description' => $values->description,
                'created_at' => new \DateTime(),
            ]);
            $this->flashMessage('Tip byl přidán', 'success');
        }

        $this->redirect('Admin:maintenance');
    }

    public function renderDeleteMaintenance(int $id): void
    {
        $item = $this->database->table('maintenance')->get($id);
        if ($item) {
            $item->delete();
            $this->flashMessage('Tip byl smazán', 'success');
        }
        $this->redirect('Admin:maintenance');
    }

    // === KONTAKTY ===

    public function renderContacts(): void
    {
        $this->template->contacts = $this->database->table('contacts')
            ->order('created_at DESC')
            ->fetchAll();
    }

    public function renderDeleteContact(int $id): void
    {
        $contact = $this->database->table('contacts')->get($id);
        if ($contact) {
            $contact->delete();
            $this->flashMessage('Kontakt byl smazán', 'success');
        }
        $this->redirect('Admin:contacts');
    }



    public function categoryFormSucceeded(Form $form, \stdClass $values)
    {
        $id = $this->getParameter('id');
        
        if ($id) {
            // Úprava kategorie
            $this->database->query('UPDATE gallery_categories SET', [
                'name' => $values->name,
                'slug' => $values->slug,
                'description' => $values->description,
                'sort_order' => $values->sort_order,
                'active' => $values->active ? 1 : 0
            ], 'WHERE id = ?', $id);
            $this->flashMessage('Kategorie byla upravena', 'success');
        } else {
            // Přidání kategorie
            $this->database->query('INSERT INTO gallery_categories', [
                'name' => $values->name,
                'slug' => $values->slug,
                'description' => $values->description,
                'sort_order' => $values->sort_order,
                'active' => $values->active ? 1 : 0,
                'created_at' => new \DateTime()
            ]);
            $this->flashMessage('Kategorie byla přidána', 'success');
        }
        
        $this->redirect('Admin:categories');
    }

    public function renderDeleteCategory(int $id)
    {
        $category = $this->database->query('SELECT * FROM gallery_categories WHERE id = ?', $id)->fetch();
        if ($category) {
            // Načtení obrázků v kategorii pro smazání souborů
            $images = $this->database->query('SELECT * FROM gallery_images WHERE category_id = ?', $id)->fetchAll();
            
            foreach ($images as $image) {
                $filePath = $this->uploadDir . $image->filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Smazání obrázků z databáze
            $this->database->query('DELETE FROM gallery_images WHERE category_id = ?', $id);
            
            // Smazání kategorie
            $this->database->query('DELETE FROM gallery_categories WHERE id = ?', $id);
            
            $this->flashMessage('Kategorie byla smazána', 'success');
        }
        
        $this->redirect('Admin:categories');
    }

    // OBRÁZKY
    public function renderImages(int $categoryId = null)
    {
        if ($categoryId) {
            $this->template->images = $this->database->query('
                SELECT gi.*, gc.name as category_name 
                FROM gallery_images gi 
                JOIN gallery_categories gc ON gi.category_id = gc.id 
                WHERE gi.category_id = ? 
                ORDER BY gi.sort_order ASC, gi.created_at DESC
            ', $categoryId)->fetchAll();
            
            $category = $this->database->query('SELECT * FROM gallery_categories WHERE id = ?', $categoryId)->fetch();
            $this->template->currentCategory = $category;
        } else {
            $this->template->images = $this->database->query('
                SELECT gi.*, gc.name as category_name 
                FROM gallery_images gi 
                JOIN gallery_categories gc ON gi.category_id = gc.id 
                ORDER BY gi.sort_order ASC, gi.created_at DESC
            ')->fetchAll();
        }
        
        $this->template->categories = $this->database->query('
            SELECT * FROM gallery_categories 
            WHERE active = 1 
            ORDER BY sort_order ASC, name ASC
        ')->fetchAll();
    }

    public function renderAddImage(int $categoryId = null)
    {
        $this->template->categories = $this->database->query('
            SELECT * FROM gallery_categories 
            WHERE active = 1 
            ORDER BY sort_order ASC, name ASC
        ')->fetchAll();
        
        if ($categoryId) {
            $this->template->selectedCategoryId = $categoryId;
        }
    }

    public function renderEditImage(int $id)
    {
        $image = $this->database->query('SELECT * FROM gallery_images WHERE id = ?', $id)->fetch();
        if (!$image) {
            $this->error('Obrázek nebyl nalezen');
        }
        
        $this->template->image = $image;
        $this->template->categories = $this->database->query('
            SELECT * FROM gallery_categories 
            WHERE active = 1 
            ORDER BY sort_order ASC, name ASC
        ')->fetchAll();
        
        // Nastavení hodnot formuláře
        $form = $this->getComponent('imageForm');
        $form->setDefaults([
            'category_id' => $image->category_id,
            'title' => $image->title,
            'description' => $image->description,
            'alt_text' => $image->alt_text,
            'sort_order' => $image->sort_order,
            'active' => (bool)$image->active
        ]);
    }

    protected function createComponentImageForm(): Form
    {
        $form = new Form;
        
        $categories = $this->database->query('
            SELECT id, name FROM gallery_categories 
            WHERE active = 1 
            ORDER BY sort_order ASC, name ASC
        ')->fetchPairs('id', 'name');
        
        $form->addSelect('category_id', 'Kategorie:', $categories)
            ->setRequired('Vyberte kategorii');
        
        $form->addText('title', 'Název obrázku:')
            ->setRequired('Zadejte název obrázku');
        
        $form->addTextArea('description', 'Popis:')
            ->setHtmlAttribute('rows', 3);
        
        $form->addText('alt_text', 'Alt text:');
        
        $form->addUpload('image', 'Obrázek:')
            ->addRule($form::IMAGE, 'Nahrajte platný obrázek')
            ->addRule($form::MAX_FILE_SIZE, 'Maximální velikost je 5 MB', 5 * 1024 * 1024);
        
        $form->addInteger('sort_order', 'Pořadí:')
            ->setDefaultValue(0);
        
        $form->addCheckbox('active', 'Aktivní')
            ->setDefaultValue(true);
        
        $form->addSubmit('save', 'Uložit');
        
        $form->onSuccess[] = [$this, 'imageFormSucceeded'];
        return $form;
    }

    public function imageFormSucceeded(Form $form, \stdClass $values)
    {
        $id = $this->getParameter('id');
        $upload = $values->image;
        
        $filename = null;
        $originalName = null;
        
        if ($upload && $upload->isOk()) {
            // Generování unikátního názvu souboru
            $filename = Random::generate(10) . '_' . $this->createSlug($upload->getName());
            $filepath = $this->uploadDir . $filename .'.png';
            
            // Uložení a změna velikosti obrázku
            $image = Image::fromFile($upload->getTemporaryFile());
            $image->resize(800, 600, Image::OrSmaller);
            $image->save($filepath);
            
            $originalName = $upload->getName();
        }
        
        if ($id) {
            // Úprava obrázku
            $oldImage = $this->database->query('SELECT * FROM gallery_images WHERE id = ?', $id)->fetch();
            
            // Smazání starého souboru pokud byl nahráván nový
            if ($filename && $oldImage->filename) {
                $oldFilePath = $this->uploadDir . $oldImage->filename;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            
            $updateData = [
                'category_id' => $values->category_id,
                'title' => $values->title,
                'description' => $values->description,
                'alt_text' => $values->alt_text,
                'sort_order' => $values->sort_order,
                'active' => $values->active ? 1 : 0
            ];
            
            // Přidání filename pouze pokud byl nahráván nový obrázek
            if ($filename) {
                $updateData['filename'] = $filename;
                $updateData['original_name'] = $originalName;
            }
            
            $this->database->query('UPDATE gallery_images SET', $updateData, 'WHERE id = ?', $id);
            $this->flashMessage('Obrázek byl upraven', 'success');
        } else {
            // Přidání obrázku
            if (!$filename) {
                $form->addError('Je nutné nahrát obrázek');
                return;
            }
            
            $this->database->query('INSERT INTO gallery_images', [
                'category_id' => $values->category_id,
                'title' => $values->title,
                'description' => $values->description,
                'filename' => $filename.'.png',
                'original_name' => $originalName,
                'alt_text' => $values->alt_text,
                'sort_order' => $values->sort_order,
                'active' => $values->active ? 1 : 0,
                'created_at' => new \DateTime()
            ]);
            $this->flashMessage('Obrázek byl přidán', 'success');
        }
        
        $this->redirect('Admin:images', ['categoryId' => $values->category_id]);
    }

    public function renderDeleteImage(int $id)
    {
        $image = $this->database->query('SELECT * FROM gallery_images WHERE id = ?', $id)->fetch();
        if ($image) {
            // Smazání souboru
            $filePath = $this->uploadDir . $image->filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $categoryId = $image->category_id;
            
            // Smazání záznamu z databáze
            $this->database->query('DELETE FROM gallery_images WHERE id = ?', $id);
            
            $this->flashMessage('Obrázek byl smazán', 'success');
        }
        
        $this->redirect('Admin:images');
    }
    
    /**
     * Vytvoří slug bez závislosti na intl rozšíření
     */
    private function createSlug(string $text): string
    {
        // Převod českých znaků na ASCII ekvivalenty
        $replacements = [
            'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 
            'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's', 
            'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z',
            'Á' => 'A', 'Č' => 'C', 'Ď' => 'D', 'É' => 'E', 'Ě' => 'E', 
            'Í' => 'I', 'Ň' => 'N', 'Ó' => 'O', 'Ř' => 'R', 'Š' => 'S', 
            'Ť' => 'T', 'Ú' => 'U', 'Ů' => 'U', 'Ý' => 'Y', 'Ž' => 'Z'
        ];
        
        $text = strtr($text, $replacements);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
}