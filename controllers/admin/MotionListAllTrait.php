<?php

namespace app\controllers\admin;

use app\models\db\Consultation;
use app\models\db\User;
use app\models\forms\AdminMotionFilterForm;
use yii\web\Response;

/**
 * @property Consultation $consultation
 * @method showErrorpage(int $code, string $message)
 * @method render(string $view, array $options)
 * @method renderPartial(string $view, array $options)
 */
trait MotionListAllTrait
{
    /**
     */
    protected function actionListallMotions()
    {
        if (isset($_REQUEST['motionScreen'])) {
            $motion = $this->consultation->getMotion($_REQUEST['motionScreen']);
            if (!$motion) {
                return;
            }
            $motion->setScreened();
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_screened'));
        }
        if (isset($_REQUEST['motionUnscreen'])) {
            $motion = $this->consultation->getMotion($_REQUEST['motionUnscreen']);
            if (!$motion) {
                return;
            }
            $motion->setUnscreened();
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_unscreened'));
        }
        if (isset($_REQUEST['motionDelete'])) {
            $motion = $this->consultation->getMotion($_REQUEST['motionDelete']);
            if (!$motion) {
                return;
            }
            $motion->setDeleted();
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_deleted'));
        }

        if (!isset($_REQUEST['motions']) || !isset($_REQUEST['save'])) {
            return;
        }
        if (isset($_REQUEST['screen'])) {
            foreach ($_REQUEST['motions'] as $motionId) {
                $motion = $this->consultation->getMotion($motionId);
                if (!$motion) {
                    continue;
                }
                $motion->setScreened();
            }
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_screened_pl'));
        }

        if (isset($_REQUEST['unscreen'])) {
            foreach ($_REQUEST['motions'] as $motionId) {
                $motion = $this->consultation->getMotion($motionId);
                if (!$motion) {
                    continue;
                }
                $motion->setUnscreened();
            }
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_unscreened_pl'));
        }

        if (isset($_REQUEST['delete'])) {
            foreach ($_REQUEST['motions'] as $motionId) {
                $motion = $this->consultation->getMotion($motionId);
                if (!$motion) {
                    continue;
                }
                $motion->setDeleted();
            }
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_deleted_pl'));
        }
    }


    /**
     */
    protected function actionListallAmendments()
    {
        if (isset($_REQUEST['amendmentScreen'])) {
            $amendment = $this->consultation->getAmendment($_REQUEST['amendmentScreen']);
            if (!$amendment) {
                return;
            }
            $amendment->setScreened();
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_am_screened'));
        }
        if (isset($_REQUEST['amendmentUnscreen'])) {
            $amendment = $this->consultation->getAmendment($_REQUEST['amendmentUnscreen']);
            if (!$amendment) {
                return;
            }
            $amendment->setUnscreened();
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_am_unscreened'));
        }
        if (isset($_REQUEST['amendmentDelete'])) {
            $amendment = $this->consultation->getAmendment($_REQUEST['amendmentDelete']);
            if (!$amendment) {
                return;
            }
            $amendment->setDeleted();
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_am_deleted'));
        }
        if (!isset($_REQUEST['amendments']) || !isset($_REQUEST['save'])) {
            return;
        }
        if (isset($_REQUEST['screen'])) {
            foreach ($_REQUEST['amendments'] as $amendmentId) {
                $amendment = $this->consultation->getAmendment($amendmentId);
                if (!$amendment) {
                    continue;
                }
                $amendment->setScreened();
            }
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_am_screened_pl'));
        }

        if (isset($_REQUEST['unscreen'])) {
            foreach ($_REQUEST['amendments'] as $amendmentId) {
                $amendment = $this->consultation->getAmendment($amendmentId);
                if (!$amendment) {
                    continue;
                }
                $amendment->setUnscreened();
            }
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_am_unscreened_pl'));
        }

        if (isset($_REQUEST['delete'])) {
            foreach ($_REQUEST['amendments'] as $amendmentId) {
                $amendment = $this->consultation->getAmendment($amendmentId);
                if (!$amendment) {
                    continue;
                }
                $amendment->setDeleted();
            }
            \yii::$app->session->setFlash('success', \Yii::t('admin', 'list_am_deleted_pl'));
        }
    }


    /**
     * @return string
     */
    public function actionListall()
    {
        if (!User::currentUserHasPrivilege($this->consultation, User::PRIVILEGE_MOTION_EDIT)) {
            $this->showErrorpage(403, \Yii::t('admin', 'no_acccess'));
            return '';
        }

        $this->actionListallMotions();
        $this->actionListallAmendments();

        $search = new AdminMotionFilterForm($this->consultation, $this->consultation->motions, true);
        if (isset($_REQUEST['Search'])) {
            $search->setAttributes($_REQUEST['Search']);
        }

        return $this->render('list_all', [
            'entries' => $search->getSorted(),
            'search'  => $search,
        ]);
    }

    /**
     * @return string
     */
    public function actionOdslistall()
    {
        \yii::$app->response->format = Response::FORMAT_RAW;
        \yii::$app->response->headers->add('Content-Type', 'application/vnd.oasis.opendocument.spreadsheet');
        \yii::$app->response->headers->add('Content-Disposition', 'attachment;filename=motions.ods');
        \yii::$app->response->headers->add('Cache-Control', 'max-age=0');

        return $this->renderPartial('ods_list_all', [
            'items'      => $this->consultation->getAgendaWithMotions(),
        ]);
    }
}
