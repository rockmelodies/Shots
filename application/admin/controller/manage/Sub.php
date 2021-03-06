<?php

namespace app\admin\controller\manage;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Sub extends Backend
{
    /**
     * Sub模型对象
     * @var \app\admin\model\manage\Sub
     */
    protected $model = null;
    protected $ports_model = null;
    protected $alive_model = null;

    public function _initialize($ids = null)
    {
        parent::_initialize();
        $this->model = new \app\admin\model\manage\Sub;
        $this->ports_model = new \app\admin\model\manage\Ports;
        $this->alive_model = new \app\admin\model\manage\Alive;

    }
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if(!$row){
            $this->error('不存在此条记录');
        }
        $domain = $row->domain;
        $subdomain = $row->subdomain;
        $this->view->assign('domain', $domain);
        $this->view->assign('subdomain', $subdomain);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $ip = @is_ip($params['ip']) ? $params['ip'] : null;
                $city = @$params['city'] ? $params['city'] : '未知';
                if(!$ip){
                    $this->error('请输入正确的IP地址');
                }else{
                    $where = ['subdomain'=>$subdomain];
                    $data = ['subdomain_ip'=>$ip, 'city'=>$city];
                    $this->model->isUpdate(true)->save($data, $where);
                    $this->success('成功修改域名信息');
                }
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    public function add()
    {
    }

    public function add_ports($ids = null)
    {
        $row = $this->model->get($ids);
        if(!$row){
            $this->error('不存在此条记录');
        }
        $subdomain = $row->subdomain;
        $subdomain_ip = $row->subdomain_ip;
        $this->view->assign('subdomain', $subdomain);
        $this->view->assign('subdomain_ip', $subdomain_ip);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if(!$subdomain_ip){
                    $this->error('请先添加IP地址');
                }
                $port = @intval($params['port']);
                if($port < 1 || $port > 65535) {
                    $this->error('请输入正确的端口');
                }
                $service = @$params['service'] ? $params['service'] : null;
                $product = @$params['product'] ? $params['product'] : null;
                $version = @$params['version'] ? $params['version'] : null;
                if($this->ports_model->addPorts($subdomain, $subdomain_ip, $port, $service, $product, $version)){
                    $this->success('添加端口成功');
                }else{
                    $this->error('该端口已被录入');
                }
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    public function add_alive($ids = null)
    {
        $row = $this->model->get($ids);
        if(!$row){
            $this->error('不存在此条记录');
        }
        $subdomain = $row->subdomain;
        $this->view->assign('subdomain', $subdomain);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $url = $params['url'] ? trim($params['url'], '/') : null;
                $host = parse_url($url)['host'];
                if($host){
                    $title = @$params['title'] ? $params['title'] : null;
                    $size = @$params['size'] ? $params['size'] : null;
                    $status = @$params['status'] ? $params['status'] : null;
                    $fingerprint = @$params['fingerprint'] ? $params['fingerprint'] : null;
                    if($this->alive_model->addAlive($subdomain, $url, $title, $size, $status, $fingerprint)){
                        $this->success('添加URL成功');
                    }else{
                        $this->error('该条URL记录已存在');
                    }
                }else{
                    $this->error('请检查URL格式');
                }
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    public function reset($ids = null)
    {
        if($ids){
            $where['id'] = ['in', $ids];
            $data = ['alivescan' => 'no_scan', 'portscan' => 'no_scan'];
            $this->model->isUpdate(true)->save($data, $where);
            $this->success('重置成功');
        }else{
            $this->error(__('You have no permission'));
        }
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
}
