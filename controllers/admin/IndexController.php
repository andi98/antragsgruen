<?php

namespace app\controllers\admin;

use app\components\Tools;
use app\components\UrlHelper;
use app\models\db\Amendment;
use app\models\db\AmendmentComment;
use app\models\db\Consultation;
use app\models\db\ConsultationSettingsTag;
use app\models\db\ConsultationText;
use app\models\db\Motion;
use app\models\db\MotionComment;
use app\models\AdminTodoItem;
use app\models\db\Site;
use app\models\db\User;
use app\models\exceptions\FormError;
use app\models\forms\ConsultationCreateForm;
use app\models\settings\AntragsgruenApp;

class IndexController extends AdminBase
{
    use SiteAccessTrait;

    /**
     * @return string
     */
    public function actionIndex()
    {
        /** @var AdminTodoItem[] $todo */
        $todo = [];

        if (!is_null($this->consultation)) {
            $motions = Motion::getScreeningMotions($this->consultation);
            foreach ($motions as $motion) {
                $description = \Yii::t('admin', 'todo_from') . ': ' . $motion->getInitiatorsStr();
                $todo[]      = new AdminTodoItem(
                    'motionScreen' . $motion->id,
                    $motion->getTitleWithPrefix(),
                    \Yii::t('admin', 'todo_motion_screen'),
                    UrlHelper::createUrl(['admin/motion/update', 'motionId' => $motion->id]),
                    $description
                );
            }
            $amendments = Amendment::getScreeningAmendments($this->consultation);
            foreach ($amendments as $amend) {
                $description = \Yii::t('admin', 'todo_from') . ': ' . $amend->getInitiatorsStr();
                $todo[]      = new AdminTodoItem(
                    'amendmentsScreen' . $amend->id,
                    $amend->getTitle(),
                    \Yii::t('admin', 'todo_amendment_screen'),
                    UrlHelper::createUrl(['admin/amendment/update', 'amendmentId' => $amend->id]),
                    $description
                );
            }
            $comments = MotionComment::getScreeningComments($this->consultation);
            foreach ($comments as $comment) {
                $description = \Yii::t('admin', 'todo_from') . ': ' . $comment->name;
                $todo[]      = new AdminTodoItem(
                    'motionCommentScreen' . $comment->id,
                    \Yii::t('admin', 'todo_comment_to') . ': ' . $comment->motion->getTitleWithPrefix(),
                    \Yii::t('admin', 'todo_comment_screen'),
                    $comment->getLink(),
                    $description
                );
            }
            $comments = AmendmentComment::getScreeningComments($this->consultation);
            foreach ($comments as $comment) {
                $description = 'Von: ' . $comment->name;
                $todo[]      = new AdminTodoItem(
                    'amendmentCommentScreen' . $comment->id,
                    \Yii::t('admin', 'todo_comment_to') . ': ' . $comment->amendment->getTitle(),
                    \Yii::t('admin', 'todo_comment_screen'),
                    $comment->getLink(),
                    $description
                );
            }
        }

        if (isset($_POST['flushCaches']) && User::currentUserIsSuperuser()) {
            AntragsgruenApp::flushAllCaches();
            \Yii::$app->session->setFlash('success', \Yii::t('admin', 'index_flushed_cached'));
        }

        return $this->render(
            'index',
            [
                'todo'         => $todo,
                'site'         => $this->site,
                'consultation' => $this->consultation
            ]
        );
    }

    /**
     * @return string
     * @throws \app\models\exceptions\FormError
     */
    public function actionConsultation()
    {
        $model = $this->consultation;

        $locale = Tools::getCurrentDateLocale();

        if (isset($_POST['save'])) {
            $this->saveTags($model);

            $data = $_POST['consultation'];
            $model->setAttributes($data);

            $settingsInput = (isset($_POST['settings']) ? $_POST['settings'] : []);
            $settings      = $model->getSettings();
            $settings->saveForm($settingsInput, $_POST['settingsFields']);
            $model->setSettings($settings);

            if ($model->save()) {
                $settingsInput = (isset($_POST['siteSettings']) ? $_POST['siteSettings'] : []);
                $siteSettings  = $model->site->getSettings();
                $siteSettings->saveForm($settingsInput, $_POST['siteSettingsFields']);
                $model->site->setSettings($siteSettings);
                if ($model->site->currentConsultationId == $model->id) {
                    $model->site->status = ($settings->maintainanceMode ? Site::STATUS_INACTIVE : Site::STATUS_ACTIVE);
                }
                $model->site->save();

                if (!$model->getSettings()->adminsMayEdit) {
                    foreach ($model->motions as $motion) {
                        $motion->setTextFixedIfNecessary();
                        foreach ($motion->amendments as $amend) {
                            $amend->setTextFixedIfNecessary();
                        }
                    }
                }

                $this->site->getSettings()->siteLayout = $siteSettings->siteLayout;
                $this->layoutParams->mainCssFile       = $siteSettings->siteLayout;

                $model->flushCacheWithChildren();
                \yii::$app->session->setFlash('success', \Yii::t('base', 'saved'));
            } else {
                \yii::$app->session->setFlash('error', print_r($model->getErrors(), true));
            }
        }

        return $this->render('consultation_settings', ['consultation' => $this->consultation, 'locale' => $locale]);
    }

    /**
     * @param Consultation $consultation
     */
    private function saveTags(Consultation $consultation)
    {
        if (!isset($_POST['tags'])) {
            return;
        }

        /**
         * @param int $tagId
         * @return ConsultationSettingsTag|null
         */
        $getById = function ($tagId) use ($consultation) {
            foreach ($consultation->tags as $tag) {
                if ($tag->id == $tagId) {
                    return $tag;
                }
            }
            return null;
        };

        /**
         * @param string $tagName
         * @return ConsultationSettingsTag|null
         */
        $getByName = function ($tagName) use ($consultation) {
            $tagName = mb_strtolower($tagName);
            foreach ($consultation->tags as $tag) {
                if (mb_strtolower($tag->title) == $tagName) {
                    return $tag;
                }
            }
            return null;
        };

        $foundTags = [];
        $newTags   = json_decode($_POST['tags'], true);
        foreach ($newTags as $pos => $newTag) {
            if ($newTag['id'] == 0) {
                if ($getByName($newTag['name'])) {
                    continue;
                }
                $tag                 = new ConsultationSettingsTag();
                $tag->consultationId = $consultation->id;
                $tag->title          = $newTag['name'];
                $tag->position       = $pos;
                $tag->save();
            } else {
                $tag = $getById($newTag['id']);
                if (!$tag) {
                    continue;
                }
                /** @var ConsultationSettingsTag $tag */
                $tag->position = $pos;
                $tag->save();
            }
            $foundTags[] = $tag->id;
        }

        foreach ($consultation->tags as $tag) {
            if (!in_array($tag->id, $foundTags)) {
                foreach ($tag->motions as $motion) {
                    $motion->unlink('tags', $tag, false);
                }
                $tag->delete();
            }
        }

        $consultation->refresh();
    }

    /**
     * @param string $category
     * @return string
     */
    public function actionTranslation($category = 'base')
    {
        $consultation = $this->consultation;

        if (isset($_POST['save']) && isset($_POST['wordingBase'])) {
            $consultation->wordingBase = $_POST['wordingBase'];
            $consultation->save();
            \yii::$app->session->setFlash('success', \Yii::t('base', 'saved'));
        }

        if (isset($_POST['save']) && isset($_POST['string'])) {
            foreach ($_POST['string'] as $key => $val) {
                $key   = urldecode($key);
                $found = false;
                foreach ($consultation->texts as $text) {
                    if ($text->category == $category && $text->textId == $key) {
                        if ($val == '') {
                            $text->delete();
                        } else {
                            $text->text = $val;
                            $text->save();
                        }
                        $found = true;
                    }
                }
                if (!$found && $val != '') {
                    $text                 = new ConsultationText();
                    $text->consultationId = $consultation->id;
                    $text->category       = $category;
                    $text->textId         = $key;
                    $text->text           = $val;
                    $text->editDate       = date('Y-m-d H:i:s');
                    $text->save();
                }
            }
            $consultation->refresh();
            \yii::$app->session->setFlash('success', \Yii::t('base', 'saved'));
        }

        return $this->render('translation', ['consultation' => $consultation, 'category' => $category]);
    }

    /**
     * @return string
     */
    public function actionSiteconsultations()
    {
        $site = $this->site;

        $form           = new ConsultationCreateForm();
        $form->template = $this->consultation;

        if (isset($_POST['createConsultation'])) {
            $form->setAttributes($_POST['newConsultation'], true);
            $form->setAsDefault = isset($_POST['newConsultation']['setStandard']);
            if (isset($_POST['newConsultation']['template'])) {
                foreach ($this->site->consultations as $cons) {
                    if ($cons->id == $_POST['newConsultation']['template']) {
                        $form->template = $cons;
                    }
                }
            }
            try {
                $form->createConsultation();
                \yii::$app->session->setFlash('success', \Yii::t('admin', 'cons_new_created'));
            } catch (FormError $e) {
                \yii::$app->session->setFlash('error', $e->getMessage());
            }
            $this->site->refresh();
        }
        if (isset($_POST['setStandard'])) {
            if (is_array($_POST['setStandard']) && count($_POST['setStandard']) == 1) {
                $keys = array_keys($_POST['setStandard']);
                foreach ($site->consultations as $consultation) {
                    if ($consultation->id == $keys[0]) {
                        $site->currentConsultationId = $consultation->id;
                        if ($consultation->getSettings()->maintainanceMode) {
                            $site->status = Site::STATUS_INACTIVE;
                        } else {
                            $site->status = Site::STATUS_ACTIVE;
                        }
                        $site->save();
                        \yii::$app->session->setFlash('success', \Yii::t('admin', 'cons_std_set_done'));
                    }
                }
            }
            $this->site->refresh();
        }

        return $this->render('site_consultations', ['site' => $site, 'createForm' => $form]);
    }
}
