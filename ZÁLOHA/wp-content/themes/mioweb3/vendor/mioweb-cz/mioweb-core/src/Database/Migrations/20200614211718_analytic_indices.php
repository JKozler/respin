<?php

namespace Mioweb\Core\Database\Migrations;

use Phinx\Migration\AbstractMigration;

class AnalyticIndices extends AbstractMigration
{
    public function up()
	{
		$this->table('core_events')
			->addIndex('group_id')
			->addIndex('target_event_name')
			->addIndex('target_event_extern_id')
			->update();

		$this->table('core_event_tags')
			->addIndex('group_id')
			->update();
	}

	public function down()
	{
	}

}
