<?php

namespace Xworkflow\Installer;

use Xworkflow\Core\LanguageManager;
use Xworkflow\Core\XCubeUtils;

/**
 * abstract module installer class.
 */
abstract class AbstractInstaller
{
    /**
     * module log.
     *
     * @var object
     */
    public $mLog = null;

    /**
     * flag for force mode.
     *
     * @var bool
     */
    protected $mForceMode = false;

    /**
     * xoops module.
     *
     * @var object
     */
    protected $mXoopsModule = null;

    /**
     * language manager.
     *
     * @var object
     */
    protected $mLangMan = null;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->mLog = new InstallLog();
    }

    /**
     * set current xoops module.
     *
     * @param object &$xoopsModule
     */
    public function setCurrentXoopsModule(&$xoopsModule)
    {
        $this->mXoopsModule = &$xoopsModule;
    }

    /**
     * set force mode.
     *
     * @param bool $isForceMode
     */
    public function setForceMode($isForceMode)
    {
        $this->mForceMode = $isForceMode;
    }

    /**
     * install tables information.
     */
    protected function _installTables()
    {
        InstallUtils::installSQLAutomatically($this->mXoopsModule, $this->mLog);
    }

    /**
     * install module information.
     *
     * @return bool
     */
    protected function _installModule()
    {
        $moduleHandler = &xoops_gethandler('module');
        if (!$moduleHandler->insert($this->mXoopsModule)) {
            $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_MODULE_INSTALLED'));

            return false;
        }
        $gpermHandler = &xoops_gethandler('groupperm');
        if ($this->mXoopsModule->getInfo('hasAdmin')) {
            // grant administrator privilages to XOOPS_GROUP_ADMIN group.
            $adminPerm = $this->_createPermission(XOOPS_GROUP_ADMIN);
            $adminPerm->set('gperm_name', 'module_admin');
            if (!$gpermHandler->insert($adminPerm)) {
                $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_PERM_ADMIN_SET'));
            }
        }
        if ($this->mXoopsModule->getInfo('hasMain')) {
            if ($this->mXoopsModule->getInfo('read_any')) {
                // grant module read privilages to all groups.
                $memberHandler = &xoops_gethandler('member');
                $groupObjects = $memberHandler->getGroups();
                foreach ($groupObjects as $group) {
                    $readPerm = $this->_createPermission($group->get('groupid'));
                    $readPerm->set('gperm_name', 'module_read');
                    if (!$gpermHandler->insert($readPerm)) {
                        $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_PERM_READ_SET'));
                    }
                }
            } else {
                // grant module read privilages to XOOPS_GROUP_ADMIN and XOOPS_GROUP_USERS groups.
                foreach (array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS) as $group) {
                    $readPerm = $this->_createPermission($group);
                    $readPerm->set('gperm_name', 'module_read');
                    if (!$gpermHandler->insert($readPerm)) {
                        $this->mLog->addError($this->mLangMan->get('INSTALL_ERROR_PERM_READ_SET'));
                    }
                }
            }
        }

        return true;
    }

    /**
     * create permission.
     *
     * @param int $gid
     *
     * @return object&
     */
    protected function _createPermission($gid)
    {
        $gpermHandler = &xoops_gethandler('groupperm');
        $perm = $gpermHandler->create();
        $perm->set('gperm_groupid', $gid);
        $perm->set('gperm_itemid', $this->mXoopsModule->get('mid'));
        $perm->set('gperm_modid', 1);

        return $perm;
    }

    /**
     * install templates.
     */
    protected function _installTemplates()
    {
        InstallUtils::installAllOfModuleTemplates($this->mXoopsModule, $this->mLog);
    }

    /**
     * install blocks.
     */
    protected function _installBlocks()
    {
        InstallUtils::installAllOfBlocks($this->mXoopsModule, $this->mLog);
    }

    /**
     * install preferences.
     */
    protected function _installPreferences()
    {
        InstallUtils::installAllOfConfigs($this->mXoopsModule, $this->mLog);
    }

    /**
     * process report.
     */
    protected function _processReport()
    {
        if (!$this->mLog->hasError()) {
            $this->mLog->addReport(XCubeUtils::formatString($this->mLangMan->get('INSTALL_MSG_MODULE_INSTALLED'), $this->mXoopsModule->getInfo('name')));
        } elseif (is_object($this->mXoopsModule)) {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_INSTALLED'), $this->mXoopsModule->getInfo('name')));
        } else {
            $this->mLog->addError(XCubeUtils::formatString($this->mLangMan->get('INSTALL_ERROR_MODULE_INSTALLED'), 'something'));
        }
    }

    /**
     * execute install.
     *
     * @return bool
     */
    public function executeInstall()
    {
        $dirname = $this->mXoopsModule->get('dirname');
        $this->mLangMan = new LanguageManager($dirname, 'install');
        $this->mLangMan->load();
        $this->_installTables();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installModule();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installTemplates();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installBlocks();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_installPreferences();
        if (!$this->mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        $this->_processReport();

        return true;
    }
}
