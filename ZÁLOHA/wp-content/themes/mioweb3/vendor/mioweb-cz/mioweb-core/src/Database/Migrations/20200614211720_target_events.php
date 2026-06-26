<?php

namespace Mioweb\Core\Database\Migrations;

use Phinx\Migration\AbstractMigration;

class TargetEvents extends AbstractMigration
{
    public function up()
	{
		if (!$this->table('core_events')->hasColumn('next_event_id')) {
			$this->table('core_events')
				->addColumn('next_event_id', 'binary', ['limit' => 16, 'null' => true])
				->addForeignKey('next_event_id', 'core_events', 'id', ['update' => 'CASCADE', 'delete' => 'SET_NULL'])
				->addColumn('target_event_id', 'binary', ['limit' => 16, 'null' => true])
				->addForeignKey('target_event_id', 'core_events', 'id', ['update' => 'CASCADE', 'delete' => 'SET_NULL'])
				->update();
		}
	}

	public function down()
	{
	}

}
