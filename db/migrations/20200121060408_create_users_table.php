<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // create the table
        $users = $this->table('users');
        $users->addColumn('username', 'string', ['limit' => 20])
              ->addColumn('password', 'string', ['limit' => 40])
              ->addColumn('password_salt', 'string', ['limit' => 40])
              ->addColumn('email', 'string', ['limit' => 100])
              ->addColumn('first_name', 'string', ['limit' => 30])
              ->addColumn('last_name', 'string', ['limit' => 30])
              ->addColumn('created', 'datetime')
              ->addColumn('updated', 'datetime', ['null' => true])
              ->addIndex(['username', 'email'], ['unique' => true])
              ->create();
    }
}
