<?php

namespace Mioweb\Core\Database\Migrations;

use Phinx\Migration\AbstractMigration;

class Analytics extends AbstractMigration
{

	public function up()
	{
		if ($this->hasTable('core_events')) {
			return;
		}

		$this->table('core_users', ['id' => false, 'primary_key' => 'id'])
			->addColumn('id', 'binary', ['limit' => 16, 'null' => false])
			->addColumn('extern_id', 'string', ['length' => 100])
			->addIndex('extern_id', ['unique' => true])
			->create();

		$this->table('core_cookies', ['id' => false, 'primary_key' => 'id'])
			->addColumn('id', 'binary', ['limit' => 16, 'null' => false])
			->addColumn('user_id', 'binary', ['limit' => 16, 'null' => true])
			->addForeignKey('user_id', 'core_users', 'id', ['update' => 'CASCADE', 'delete' => 'SET_NULL'])
			->create();

		$this->table('core_events', ['id' => false, 'primary_key' => 'id'])
			->addColumn('id', 'binary', ['limit' => 16, 'null' => false])
			->addColumn('cookie_id', 'binary', ['limit' => 16])
			->addForeignKey('cookie_id', 'core_cookies', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE'])
			->addColumn('user_id', 'binary', ['limit' => 16, 'null' => true])
			->addForeignKey('user_id', 'core_users', 'id', ['update' => 'CASCADE', 'delete' => 'SET_NULL'])
			->addColumn('group_id', 'binary', ['limit' => 16])
			->addColumn('name', 'string', ['length' => 100])
			->addIndex('name')
			->addColumn('extern_id', 'string', ['length' => 100, 'null' => true])
			->addIndex('extern_id')
			->addColumn('unique_id', 'string', ['length' => 100, 'null' => true])
			->addIndex('unique_id')
			->addColumn('data', 'text')
			->addColumn('target_event_name', 'string', ['length' => 100, 'null' => true])
			->addColumn('target_event_extern_id', 'string', ['length' => 100, 'null' => true])
			->addColumn('timestamp', 'datetime')
			->addIndex('timestamp')
			->create();

		$this->table('core_event_tags', ['id' => false, 'primary_key' => 'id'])
			->addColumn('id', 'binary', ['limit' => 16, 'null' => false])
			->addColumn('event_id', 'binary', ['limit' => 16])
			->addForeignKey('event_id', 'core_events', 'id', ['update' => 'CASCADE', 'delete' => 'CASCADE'])
			->addColumn('group_id', 'binary', ['limit' => 16])
			->addColumn('name', 'string', ['length' => 100])
			->addIndex('name')
			->addColumn('value', 'string', ['length' => 100])
			->addIndex('value')
			->addColumn('timestamp', 'datetime')
			->addIndex('timestamp')
			->create();
	}

	public function down()
	{
	}

}
