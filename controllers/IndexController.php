<?php

namespace passport\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use backend\form\Plupload;
use passport\classes\form\PassportForm;
use passport\classes\model\PassportTable;
use system\classes\Syslog;
use wulaphp\auth\Passport;
use wulaphp\db\sql\Condition;
use wulaphp\io\Ajax;
use wulaphp\io\LocaleUploader;
use wulaphp\mvc\view\JsonView;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * 默认控制器.
 * @acl     m:system/passport
 * @accept  passport\classes\form\PassportForm
 */
class IndexController extends IFramePageController {
	use JQueryValidatorController, Plupload;

	public function index() {
		$data  = [];
		$forms = apply_filter('passport\advanceSearchForm', []);
		if ($forms) {
			foreach ($forms as $f) {
				$data['forms'][] = BootstrapFormRender::v($f);
			}
		}

		return $this->render($data);
	}

	//修改用户状态
	public function setStatus($status, $ids = '') {
		$ids = safe_ids2($ids);
		if ($ids) {
			$status = $status === '1' ? 1 : 0;

			if ($ids) {
				(new PassportTable())->db()->update('{passport}')->set(['status' => $status])->where(['id IN' => $ids])->exec();
				fire('passport\onChangeStatus', $ids);
			}

			return Ajax::reload('#table', $status == '1' ? '所选用户已激活' : '所选用户已禁用');
		} else {
			return Ajax::error('未指定用户');
		}
	}

	public function edit($id) {
		$form = new PassportForm(true);
		if ($id) {
			$admin = $form->get($id);
			$user  = $admin->get(0);

			$data['avatar'] = $user['avatar'];
			$user['email']  = $form->db()->select('open_id')->from('{oauth}')->where([
				'passport_id' => $id,
				'type'        => 'email'
			])->get('open_id');
			if ($user['parent']) {
				$user['recom'] = PassportTable::toRecCode($user['parent']);
			}
			$form->inflateByData($user);
			$form->removeRule('password', 'required');
		}
		$data['form']  = BootstrapFormRender::v($form);
		$data['id']    = $id;
		$data['rules'] = $form->encodeValidatorRule($this);

		return view($data);
	}

	//保存数据
	public function savePost($id) {
		$form = new PassportForm(true);
		$user = $form->inflate();
		try {
			if ($id) {
				$form->removeRule('password', 'required');
			}
			$form->validate($user);
			if (($id && $user['password']) || !$id) {
				$user['passwd'] = Passport::passwd($user['password']);
			}
			unset($user['password'], $user['password1']);
			$table = new PassportTable();
			if ($id) {
				$rst = $table->updateAccount($user);
			} else {
				unset($user['id']);
				$avatar = sess_del('uploaded_avatar1');
				if ($avatar) {
					$user['avatar'] = $avatar;
				}
				$user['status'] = 1;
				$rst            = $table->newAccount($user);
			}
			if (!$rst) {
				return Ajax::error($table->lastError());
			} else {
				$id ? Syslog::info('更新通行证:' . $id, $this->passport->uid, 'passport') : Syslog::info('创建通行证:' . $user['username'], $this->passport->uid, 'passport');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('PassportForm', $ve->getErrors());
		} catch (\PDOException $pe) {
			return Ajax::error($pe->getMessage());
		}

		return Ajax::reload('#table', $id ? '用户修改成功' : '新用户已经成功创建');
	}

	/**
	 * 更新头像.
	 *
	 * @param int $uid
	 *
	 * @return array|\wulaphp\mvc\view\JsonView
	 */
	public function updateAvatar($uid = 0) {
		$rst = $this->upload(null, 512000);
		if (isset($rst['error']) && $rst['error']['code'] == 422) {
			return new JsonView($rst, [], 422);
		}
		if ($rst['done']) {
			$url = $rst['result']['url'];
			if ($uid) {
				$table  = new PassportTable();
				$avatar = $table->get(['id' => $uid])['avatar'];
				$table->updateAccount(['avatar' => $url, 'id' => $uid]);
				Syslog::info('更新头像:' . $uid, $this->passport->uid, 'passport');
			} else {
				$avatar                       = sess_get('uploaded_avatar1');
				$_SESSION['uploaded_avatar1'] = $url;
			}
			if ($avatar && !preg_match('#^(/|https?://).+#', $avatar)) {
				$locale = new LocaleUploader();
				$locale->delete($avatar);
			}
		}

		return $rst;
	}

	//删除头像
	public function delAvatar($uid = '') {
		if ($uid) {
			$table  = new PassportTable();
			$avatar = $table->get(['id' => $uid])['avatar'];
			$table->updateAccount(['avatar' => '', 'id' => $uid]);
			Syslog::info('删除头像:' . $uid, $this->passport->uid, 'passport');
		} else {
			$avatar = sess_del('uploaded_avatar1');
		}
		if ($avatar && !preg_match('#^(/|https?://).+#', $avatar)) {
			$locale = new LocaleUploader();
			$locale->delete($avatar);
		}

		return Ajax::success();
	}

	/**
	 * 删除用户.
	 *
	 * @param string $ids
	 *
	 * @acl d:system/account
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function del($ids = '') {
		$ids = safe_ids2($ids);
		if ($ids) {
			if ($ids) {
				$table = new PassportTable();
				$rst   = $table->deleteAccount($ids);
				if ($rst) {
					Syslog::info('删除通行证:' . implode(',', $ids), $this->passport->uid, 'passport');

					return Ajax::reload('#table', '所选用户已删除');
				} else {
					$error = $table->lastError();

					return Ajax::error($error ? $error : '删除用户出错，请找系统管理员');
				}
			}
		}

		return Ajax::error('未指定用户');
	}

	public function data($q, $status, $count) {
		$passport = new PassportTable();
		$sql      = $passport->alias('PAS')->select('PAS.*,OAUTH.open_id AS email');
		$sql->join('{oauth} AS OAUTH', "PAS.id = OAUTH.passport_id AND OAUTH.type = 'email'");
		$where = [];
		if ($status == 0) {
			$where['PAS.status'] = 0;
		} else {
			$where['PAS.status'] = 1;
		}
		if ($q) {
			$qw = Condition::parseSearchExpression($q, [
				'注册时间' => '@PAS.create_time',
				'最后登录' => '@PAS.login_time',
				'设备'   => 'PAS.device',
				'推荐人'  => 'PAS.spm',
				'推荐码'  => 'PAS.rec_code',
				'手机'   => 'PAS.phone',
				'邮箱'   => 'OAUTH.open_id',
				'渠道'   => 'PAS.channel',
				'姓名'   => 'PAS.nickname',
				'账户'   => 'PAS.username'
			]);
			if ($qw) {
				$sql->where($qw);
			} else {
				$where['PAS.username LIKE'] = "%$q%";
			}
		}
		$sql->where($where);
		//通知第三方修改
		fire('alter_passport_query', $sql);

		$sql->sort();
		$sql->page();
		if ($count) {
			$data['total'] = $sql->count('PAS.id');
		}
		$data['rows'] = $sql->toArray();

		return view($data);
	}
}