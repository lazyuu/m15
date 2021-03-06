<?php
namespace app\admin\model;
use think\Model;
use think\Validate;
/*
 * 模版 表数据模型
 **/
class UserVip extends Model
{
    //声明主键
    protected $pk = 'vip_id';
    //自动写入时间戳
    protected $autoWriteTimestamp = true;
    //声明添加时间字段
    protected $createTime = 'vip_time';
    //声明修改时间字段
    //protected $updateTime = 'frame_time';
    //关闭自动写入
    protected $updateTime = false;
    //声明表名
    protected $table = 'm15_user_vip';
    protected $rule = [
        'vip_user|关联用户'        => 'require|number'
    ];

    /**
     * 分页读取数据
     * @param array $where   条件
     * @param int   $page    第几页
     * @param int   $limit   每页的条数
     **/
    public function GetListByPage($where=array(), $page=1, $limit=10, $order="vip_id desc")
    {   
        return $this->where($where)->page($page,$limit)->order($order)->select();
    }

    //获取数据列表，不分页
    public function GetDataList($where=array(), $order="vip_id desc")
    {
        return $this->where($where)->order($order)->select();
    }

    /**
     * 根据条件获取一条数据
     * @param array $param 主键
     **/
    public function GetOneData($where=array())
    {
        return $this->where($where)->find();
    }

    /**
     * 根据主键获取一条数据
     * @param array $param 主键
     **/
    public function GetOneDataById($id=0)
    {
        return $this->where($this->pk,$id)->find();
    }

    /**
     * 获取一列数据
     * @param array  $param 获取条件
     * @param string $field 字段名
     **/
    public function GetColumn($param,$field)
    {
        return $this->where($param)->column($field);
    }

    /**
     * 根据条件获取一个字段
     * @param array $param 主键
     **/
    public function GetField($where=array(),$field)
    {
        return $this->where($where)->value($field);
    }

    /**
     * 获取总条数
     * @param array $param 主键
     **/
    public function GetCount($where=array())
    {
        return $this->where($where)->count();
    }

    /**
     * 添加操作
     * @param array $param 需要添加的数组
     **/
    public function CreateData($param)
    {
        $validate = new Validate($this->rule);
        $result   = $validate->check($param);

        if(!$result)
            return array('code'=>0,'msg'=>$validate->getError());

        $res = $this->allowField(true)->save($param);

        $id = $this->getLastInsID();

        return $res === false ? array('code'=>0,'msg'=>$this->getError()) : array('code'=>1,'msg'=>'添加成功','id'=>$id);
    }

    /**
     * 修改操作
     * @param array $param 需要修改的数组
     **/
    public function UpdateData($param)
    {
        
        $res = $this->allowField(true)->save($param, [$this->pk => $param[$this->pk]]);

        return $res === false ? array('code'=>0,'msg'=>$this->getError()) : array('code'=>1,'msg'=>'修改成功');
    }

    /**
     * 字段自增
     * @param array  $param   更新条件
     * @param string $field   自增字段
     * @param int    $number  更新数量
     **/
    public function DataSetInc($param,$field,$number=1)
    {
        $res = $this->where($param)->setInc($field,$number);

        return $res === false ? array('code'=>0,'msg'=>$this->getError()) : array('code'=>1,'msg'=>'修改成功');
    }

    /**
     * 字段自减
     * @param array  $param   更新条件
     * @param string $field   自减字段
     * @param int    $number  自减数量
     **/
    public function DataSetDec($param,$field,$number=1)
    {
        $res = $this->where($param)->setDec($field,$number);

        return $res === false ? array('code'=>0,'msg'=>$this->getError()) : array('code'=>1,'msg'=>'修改成功');
    }

    /**
     * 删除数据
     * @param int $id 删除数据的id
     **/
    public function DeleteData($id)
    {
        return $this->where($this->pk,$id)->delete() ? array('code'=>1,'msg'=>'删除成功') : array('code'=>0,'msg'=>'删除失败');
    }

    // 查询用户会员信息
    public function getUserVip($userId)
    {
        $userVipModel = new UserVip();
        $vipLevelModel = new VipLevel();

        $vipLevel = [
            'vip_one_info' => '2',
            'vip_two_info' => '3',
            'vip_three_info' => '4',
        ];

        $userInfo = $userVipModel->GetOneData(['vip_user' => $userId]);

        // 查看用户在当前时间是否是会员
        $vipInfo['vip_one_info'] = empty($userInfo['vip_one_info']) ? [] : json_decode($userInfo['vip_one_info'], true);
        $vipInfo['vip_two_info'] = empty($userInfo['vip_two_info']) ? [] : json_decode($userInfo['vip_two_info'], true);
        $vipInfo['vip_three_info'] = empty($userInfo['vip_three_info']) ? [] : json_decode($userInfo['vip_three_info'], true);

        $res = [];
        foreach ($vipInfo as $key => $value) {
            if(empty($value)) {
                continue;
            }
            if((time() > $value['vip_start']) && (time() < $value['vip_expire'])) {
                $res['vip'] = $vipLevelModel->GetOneData(['level_id' => $vipLevel[$key]]);
                $res['time'] = $value;
                break;
            }
        }
        return $res;
    }
}
