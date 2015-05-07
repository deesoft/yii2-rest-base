<?php

namespace dee\rest\base;

use Yii;
use yii\db\ActiveRecord;
use dee\base\GlobalTriggerTrait;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Description of ActiveResourceTrait
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
trait AdvanceControllerTrait
{

    use GlobalTriggerTrait,
        TransactionTrait;
    /**
     * @var string
     */
    public $expandParam;

    /**
     * Lists all models.
     * @return mixed
     */
    public function query()
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        $query = $modelClass::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->fire('query', [$dataProvider]);
        return $dataProvider;
    }

    /**
     * Displays a single model.
     * @param integer $id
     * @return mixed
     */
    public function view($id)
    {
        $model = $this->findModel($id);
        $this->fire('view', [$model]);
        if ($this->expandParam && ($attr = Yii::$app->request->getQueryParam($this->expandParam)) !== null) {
            $definition = array_merge($model->fields(), $model->extraFields());
            if (isset($definition[$attr])) {
                return is_string($definition[$attr]) ? $model->{$definition[$attr]} : call_user_func($definition[$attr], $model, $attr);
            } elseif (in_array($attr, $definition)) {
                return $model->$attr;
            }
            throw new NotFoundHttpException("Object not found: $id/$attr");
        }
        return $model;
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function create()
    {
        /* @var $model ActiveRecord */
        $model = $this->createModel();
        $this->beginTransaction();
        try {
            $this->fire('beforeCreate', [$model]);
            $model->load(Yii::$app->request->post(), '');
            $this->fire('create', [$model]);
            if ($model->save()) {
                $this->fire('created', [$model]);
                $this->commit();
                $model->refresh();
            } else {
                $this->fire('rollbackCreate', [$model]);
                $this->rollBack();
            }
        } catch (\Exception $e) {
            $this->fire('errorCreate', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

        return $model;
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function update($id)
    {
        $model = $this->findModel($id);

        $this->beginTransaction();
        try {
            $this->fire('beforeUpdate', [$model]);
            $model->load(Yii::$app->request->post(), '');
            $this->fire('update', [$model]);
            if ($model->save()) {
                $this->fire('updated', [$model]);
                $this->commit();
                $model->refresh();
            } else {
                $this->fire('rollbackUpdate', [$model]);
                $this->rollBack();
            }
        } catch (\Exception $e) {
            $this->fire('errorUpdate', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

        return $model;
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function patch($id)
    {
        $model = $this->findModel($id);

        $this->beginTransaction();
        try {
            $this->fire('beforePatch', [$model]);
            $patchs = Yii::$app->request->post();
            foreach ($patchs as $patch) {
                $this->doPatch($model, $patch);
            }
            $this->fire('patch', [$model]);
            if ($model->save()) {
                $this->fire('patched', [$model]);
                $this->commit();
                $model->refresh();
            } else {
                $this->fire('rollbackPatch', [$model]);
                $this->rollBack();
            }
        } catch (\Exception $e) {
            $this->fire('errorPatch', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

        return $model;
    }

    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function delete($id)
    {
        $model = $this->findModel($id);
        $this->beginTransaction();
        try {
            $this->fire('beforeDelete', [$model]);
            if ($model->delete()) {
                $this->fire('deleted', [$model]);
                $this->commit();
            } else {
                $this->fire('rollbackDelete', [$model]);
                $this->rollBack();
                return false;
            }
        } catch (\Exception $e) {
            $this->fire('errorDelete', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

        return true;
    }
}