<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;


/**
 * 创建模板
 * User: Yacon
 * Date: 2021-11-27
 * Time: 16:34
 */
class CreateTemplate extends Api
{
  public $restMethodList = 'POST';

  public function save()
  {
    $params = $this->selectParam([
      'type',     // 类型
      'name',     // 模板名称
      'desc',     // 模板描述
      'validate' => false, // 创建参数校验文件
      'logic'    => false, // 创建逻辑文件
      'model'    => false, // 创建模型文件
      'controller'    => true, // 创建控制器文件
    ]);

    $nowDay = date('Y-m-d'); // 创建日期
    $nowTime = date('H:i'); // 创建时间
    $pageName = $params['type']; // 空间名称
    $className = $params['name']; // 类名
    $objName = lcfirst($className); // 对象名
    $methodPrev = $pageName; // 方法前缀
    $validateToken = $pageName;
    $is_create_validate = $params['validate'] ? true : false;
    $is_create_logic = $params['logic'] ? true : false;
    $is_create_model = $params['model'] ? true : false;
    $is_create_controller = $params['controller'] ? true : false;
    $controllerFilePath = APP_PATH . "api/controller/v1/$pageName/$className.php"; // 控制器文件路径
    $logicFilePath = APP_PATH . "api/logic/$pageName/{$className}Logic.php"; // 逻辑文件路径
    $validateFilePath = APP_PATH . "common/validate/{$className}.php"; // 校验文件路径
    $modelFilePath = APP_PATH . "api/model/{$className}.php"; // 模型文件路径


    // 检查文件是否存在
    if ($is_create_controller && file_exists($controllerFilePath)) {
      $this->render(200, ['result' => '控制器已存在']);
      return;
    }

    if ($is_create_logic && file_exists($logicFilePath)) {
      $this->render(200, ['result' => '业务逻辑已存在']);
      return;
    }

    if ($is_create_validate && file_exists($validateFilePath)) {
      $this->render(200, ['result' => '参数校验已存在']);
      return;
    }

    if ($is_create_model && file_exists($modelFilePath)) {
      $this->render(200, ['result' => '模型已存在']);
      return;
    }

    $controllerTempate = <<<END
<?php
  namespace app\api\controller\\v1\\$pageName;
  use app\api\controller\Api;
  use think\Exception;
  use app\api\logic\\$pageName\\{$className}Logic;

  /**
   * {$params['desc']}-控制器
   * User: Yacon
   * Date: $nowDay
   * Time: $nowTime
   */
  class $className extends Api
  {
      public \$restMethodList = 'get|post|put|delete';

      
      public function _initialize()
      {
        parent::_initialize();
        \$this->userInfo = \$this->{$validateToken}ValidateToken();
      }

      public function index(){
        \$request = \$this->selectParam([
          'page_index'=>1,      // 当前页码
          'page_size'=>10,      // 每页条目数
          'keyword_search'=>'', // 关键词
          'start_time'=>'',     // 开始时间
          'end_time'=>'',        // 结束时间
          'is_page'=>1,        // 是否分页 1=分页 2=不分页
        ]);
        \$this->check(\$request,"{$className}.list");
        if(\$request['is_page'] == 1){
          \$result = {$className}Logic::{$methodPrev}Page(\$request,\$this->userInfo);
        }
        else{
          \$result = {$className}Logic::{$methodPrev}List(\$request,\$this->userInfo);
        }
        \$this->render(200,['result' => \$result]);
      }

      public function read(\$id){
        \$result = {$className}Logic::{$methodPrev}Detail(\$id,\$this->userInfo);
        \$this->render(200,['result' => \$result]);
      }

      public function save(){
        \$request = \$this->selectParam([]);
        \$this->check(\$request,"{$className}.save");
        \$result = {$className}Logic::{$methodPrev}Add(\$request,\$this->userInfo);
        if (isset(\$result['msg'])) {
          \$this->returnmsg(400, [], [], '', '', \$result['msg']);
        } else {
          \$this->render(200, ['result' => \$result]);
        }
      }

      public function update(\$id){
        \$request = \$this->selectParam([]);
        \$request['uuid'] = \$id;
        \$this->check(\$request,"{$className}.edit");
        \$result = {$className}Logic::{$methodPrev}Edit(\$request,\$this->userInfo);
        if (isset(\$result['msg'])) {
          \$this->returnmsg(400, [], [], '', '', \$result['msg']);
        } else {
          \$this->render(200, ['result' => \$result]);
        }
      }

      public function delete(\$id){
        \$result = {$className}Logic::{$methodPrev}Delete(\$id,\$this->userInfo);
        if (isset(\$result['msg'])) {
          \$this->returnmsg(400, [], [], '', '', \$result['msg']);
        } else {
          \$this->render(200, ['result' => \$result]);
        }
      }
  }
END;

    $logicTemplate = <<<END
<?php
  namespace app\api\logic\\{$pageName};
  use app\api\model\\{$className};
  use think\Exception;
  use think\Db;

  /**
   * {$params['desc']}-逻辑
   * User: Yacon
   * Date: $nowDay
   * Time: $nowTime
   */
  class {$className}Logic
  {
    static public function {$methodPrev}Page(\$request,\$userInfo){
      \$map['a.is_deleted'] = 1;
      \$result={$className}::build()
          ->field('*')
          ->alias('a')
          ->where(\$map)
          ->order('a.create_time desc')
          ->paginate(['list_rows' => \$request['page_size'], 'page' => \$request['page_index']]);
      return \$result;
    }
    
    static public function {$methodPrev}List(\$request,\$userInfo){
      \$map['a.is_deleted'] = 1;
      \$result={$className}::build()
          ->field('*')
          ->alias('a')
          ->where(\$map)
          ->order('a.create_time desc')
          ->select();
      return \$result;
    }

    static public function {$methodPrev}Detail(\$id,\$userInfo){
        \$result={$className}::build()
          ->field('*')
          ->alias('a')
          ->where('a.uuid',\$id)
          ->find();
        return \$result;
    }

    static public function {$methodPrev}Add(\$request,\$userInfo){
      try {
        Db::startTrans();
        \${$objName} = {$className}::build();
        \${$objName}['uuid'] = uuid();
        \${$objName}['create_time'] = now_time(time());
        \${$objName}['update_time'] = now_time(time());
        \${$objName}->save();
        Db::commit();
        return \${$objName}['uuid'];
      } catch (Exception \$e) {
          Db::rollback();
          throw new Exception(\$e->getMessage(), 500);
      }
    }

    static public function {$methodPrev}Edit(\$request,\$userInfo){
      try {
        Db::startTrans();
        \${$objName} = {$className}::build()->where(['uuid' => \$request['uuid']])->find();
        \${$objName}['update_time'] = now_time(time());
        \${$objName}->save();
        Db::commit();
        return true;
      } catch (Exception \$e) {
          Db::rollback();
          throw new Exception(\$e->getMessage(), 500);
      }
    }

    static public function {$methodPrev}Delete(\$id,\$userInfo){
      try {
        Db::startTrans();
        \${$objName} = {$className}::build()->where(['uuid'=>\$id])->find();
        \${$objName}['update_time'] = now_time(time());
        \${$objName}['is_deleted'] = 2;
        \${$objName}->save();
        Db::commit();
        return true;
      } catch (Exception \$e) {
          Db::rollback();
          throw new Exception(\$e->getMessage(), 500);
      }
    }
  }
END;

    $validateTemplate = <<<END
<?php
  namespace app\common\\validate;
  use think\Validate;

  /**
   * {$params['desc']}-校验
   * User: Yacon
   * Date: $nowDay
   * Time: $nowTime
   */
  class {$className} extends Validate
  {
    protected \$rule = [
      
    ];

    protected \$field = [
      
    ];

    protected \$message = [
      
    ];

    protected \$scene = [
      'list' => [],
      'save' => [],
      'edit' => []
    ];
  }
END;

    $modelTemplate = <<<END
<?php
  namespace app\\api\\model;

  /**
   * {$params['desc']}-模型
   * User: Yacon
   * Date: $nowDay
   * Time: $nowTime
   */
  class {$className} extends BaseModel
  {
      public static function build() {
          return new self();
      }
  }
END;


    if ($is_create_controller) {
      file_put_contents($controllerFilePath, $controllerTempate);
    }

    if ($is_create_logic) {
      file_put_contents($logicFilePath, $logicTemplate);
    }

    if ($is_create_validate) {
      file_put_contents($validateFilePath, $validateTemplate);
    }

    if ($is_create_model) {
      file_put_contents($modelFilePath, $modelTemplate);
    }

    $this->render(200, ['result' => true]);
  }
}
