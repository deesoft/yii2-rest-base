<?php

namespace dee\rest\base;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Description of BasicResourceTrait
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
trait BasicControllerTrait
{
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
        $model->load(Yii::$app->request->post(), '');
        $model->save();
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

        $model->load(Yii::$app->request->post(), '');
        $model->save();
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

        $patchs = Yii::$app->request->post();
        foreach ($patchs as $patch) {
            $this->doPatch($model, $patch);
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
        return $model->delete();
    }
}