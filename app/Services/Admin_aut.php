<?php

namespace App\Services;

use CodeIgniter\Config\BaseService;

class Admin_aut extends BaseService
{

    private static $user_record;

    private static $campoId = 'usu_id';

    private static $instance = null;

    private function __construct()
    {
        helper('url');
        helper('App\ThirdParty\Ragnos\Helpers\utiles_helper');
        $this->session = session();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Admin_aut();
        }

        return self::$instance;
    }

    public function isLoggedIn()
    {
        return !$this->id() ? false : true;
    }

    public function checkLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->session->set('bef_uri', current_url());

            redirectAndDie('admin/login', 401);
        }
    }

    public static function id()
    {
        $id = sessionValue(Admin_aut::$campoId);
        return $id;
    }

    public function getField($field)
    {
        if ($this->isLoggedIn()) {
            if (!Admin_aut::$user_record) {
                $id                     = $this->id();
                $sql                    = "select usu_id, usu_nombre, gru_nombre from gen_usuarios, gen_gruposdeusuarios where usu_grupo=gru_id and usu_id=?";
                $db                     = db_connect();
                $query                  = $db->query($sql, [$id]);
                $result                 = $query->getFirstRow('array');
                Admin_aut::$user_record = $result;
            }
            return isset(Admin_aut::$user_record[$field]) ? Admin_aut::$user_record[$field] : '';
        } else {
            return '';
        }
    }

    public function name()
    {
        return $this->getField('usu_nombre');
    }

    public function isUserInGroup($grupo)
    {
        return (trim(strtolower($this->getField('gru_nombre'))) == trim(strtolower($grupo)));
    }

    public function checkUserInGroup($grupos)
    {
        $this->checkLogin();
        $groupName = trim(strtolower($this->getField('gru_nombre')));
        if (is_array($grupos)) {
            array_map('trim', $grupos);
            array_map('strtolower', $grupos);
            if (!in_array($groupName, $grupos)) {
                redirectAndDie('admin/index', 403);
            }
        } else {
            if ($groupName != trim(strtolower($grupos))) {
                redirectAndDie('admin/index', 403);
            }
        }
    }
}
