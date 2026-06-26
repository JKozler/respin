<?php

namespace Mioweb\Core\Database\Migrations;

use Phinx\Migration\AbstractMigration;

class EventsNullableData extends AbstractMigration
{
    public function up()
	{
		$this->table('core_events')
			->changeColumn('data', 'text', ['null' => true])
			->update();

		$this->query("UPDATE `core_events` SET `data` = NULL WHERE `data` = '[]'");
	}

	public function down()
	{
	}

}
