<?php

namespace Mioweb\Core\Database\Migrations;

use Phinx\Migration\AbstractMigration;

class InitTargets extends AbstractMigration
{
    public function up()
	{
		$this->execute('
			UPDATE core_events e
			JOIN core_events e2 ON e2.group_id = e.group_id AND e2.timestamp > e.timestamp AND e.next_event_id IS NULL
			LEFT JOIN core_events e3 ON e3.group_id = e.group_id AND e3.timestamp > e.timestamp AND e3.timestamp < e2.timestamp
			SET e.next_event_id = e2.id
			WHERE e3.id IS NULL
		');
		$this->execute('
			UPDATE core_events e
			JOIN core_events e2 ON e2.group_id = e.group_id AND e2.timestamp > e.timestamp AND e2.name = e.target_event_name AND (e.target_event_extern_id IS NULL OR e2.extern_id = e.target_event_extern_id) AND e.target_event_id IS NULL
			LEFT JOIN core_events e3 ON e3.group_id = e.group_id AND e3.timestamp > e.timestamp AND e3.timestamp < e2.timestamp AND e3.name = e.target_event_name AND (e.target_event_extern_id IS NULL OR e3.extern_id = e.target_event_extern_id)
			SET e.target_event_id = e2.id
			WHERE e3.id IS NULL
		');
	}

	public function down()
	{
	}

}
