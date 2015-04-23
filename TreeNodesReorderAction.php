<?php
namespace devgroup\JsTreeWidget;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
/**
 * Helper action to change sort_order attribute via JsTree Drag&Drop
 * Example use in controller:
 * ``` php
 * public function actions()
 * {
 *     return [
 *         'move' => [
 *             'class' => TreeNodesReorderAction::className(),
 *             'class_name' => Category::className(),
 *         ],
 *         'upload' => [
 *             'class' => UploadAction::className(),
 *             'upload' => 'theme/resources/product-images',
 *         ],
 *         'remove' => [
 *             'class' => RemoveAction::className(),
 *             'uploadDir' => 'theme/resources/product-images',
 *         ],
 *         'save-info' => [
 *             'class' => SaveInfoAction::className(),
 *         ],
 *     ];
 * }
 * ```
 */

class TreeNodesReorderAction extends Action{
    public $className = null;
    public $modelSortOrderField = 'sort_order';
    public $sortOrder = [];

    public function init()
    {
        if (!isset($this->className)) {
            throw new InvalidConfigException("Model name should be set in controller actions");
        }
        if (!class_exists($this->className)) {
            throw new InvalidConfigException("Model class does not exists");
        }
        $this->sortOrder = Yii::$app->request->post('order');
        if (empty($this->sortOrder)) {
            throw new NotFoundHttpException;
        }
    }

    public function run()
    {
        $class = $this->className;
        $case = 'CASE `id`';
        foreach ($this->sortOrder as $id => $sort_order) {
            $case .= ' when "' . $id . '" then "' . $sort_order . '"';
        }
        $case .= ' END';
        $sql = "UPDATE "
            . $class::tableName()
            . " SET sort_order = "
            . $case
            . " WHERE id IN(" . implode(', ', array_keys($this->sortOrder))
            . ")";
        \Yii::$app->db->createCommand($sql)->execute();
    }
}