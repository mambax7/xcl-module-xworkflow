<?php

use Xworkflow\Core\Functions;
use Xworkflow\Core\XoopsUtils;

/**
 * abstract action.
 */
abstract class Xworkflow_AbstractAction
{
    /**
     * XCUBE root instance.
     *
     * @var XCube_Root
     */
    public $mRoot = null;

    /**
     * module instance.
     *
     * @var {Trustdirname}_Module
     */
    public $mModule = null;

    /**
     * asset manager instance.
     *
     * @var {Trustdirname}_AssetManager
     */
    public $mAsset = null;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->mRoot = &XCube_Root::getSingleton();
        $this->mModule = &$this->mRoot->mContext->mModule;
        $this->mAsset = &$this->mModule->mAssetManager;
    }

    /**
     * get action name.
     *
     * @return string
     */
    protected function _getActionName()
    {
        return null;
    }

    /**
     * get page title (for internal).
     *
     * @return string
     */
    protected function _getPagetitle()
    {
        return null;
    }

    /**
     * get page title.
     *
     * @return string
     */
    public function getPagetitle()
    {
        return XoopsUtils::formatPagetitle($this->mRoot->mContext->mModule->mXoopsModule->get('name'), $this->_getPagetitle(), $this->_getActionName());
    }

    /**
     * get style sheet.
     *
     * @return string
     */
    protected function _getStylesheet()
    {
        return '/modules/'.$this->mAsset->mDirname.'/index.php/css/style.css';
    }

    /**
     * set header script.
     */
    public function setHeaderScript()
    {
        $headerScript = $this->mRoot->mContext->getAttribute('headerScript');
        $headerScript->addStylesheet($this->_getStylesheet());
    }

    /**
     * prepare.
     *
     * @return bool
     */
    public function prepare()
    {
        return true;
    }

    /**
     * check whether user has permission.
     *
     * @return bool
     */
    public function hasPermission()
    {
        return true;
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        return $this->_getFrameViewStatus('NONE');
    }

    /**
     * execute.
     *
     * @return Enum
     */
    public function execute()
    {
        return $this->_getFrameViewStatus('NONE');
    }

    /**
     * execute view success.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewSuccess(&$render)
    {
    }

    /**
     * execute view error.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewError(&$render)
    {
    }

    /**
     * execute view index.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewIndex(&$render)
    {
    }

    /**
     * execute view input.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewInput(&$render)
    {
    }

    /**
     * execute view preview.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewPreview(&$render)
    {
    }

    /**
     * execute view cancel.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewCancel(&$render)
    {
    }

    /**
     * get frame view.
     *
     * @param string $name
     *
     * @return string
     */
    protected function _getFrameViewStatus($name)
    {
        return constant(strtoupper($this->mAsset->mTrustDirname).'_FRAME_VIEW_'.$name);
    }

    /**
     * get group list.
     *
     * @return string[]
     */
    protected function _getGroupList()
    {
        $member_handler = &xoops_gethandler('member');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('groupid', XOOPS_GROUP_USERS, '!='));
        $criteria->add(new Criteria('groupid', XOOPS_GROUP_ANONYMOUS, '!='));
        $groups = $member_handler->getGroupList($criteria);
        $constpref = '_MD_'.strtoupper($this->mAsset->mDirname);
        if (Functions::isExtendedGroup()) {
            $groups[0] = constant($constpref.'_LANG_GROUP_ADMIN');
        }
        ksort($groups);

        return $groups;
    }
}
