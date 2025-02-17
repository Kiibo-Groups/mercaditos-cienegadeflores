<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;
use Validator;
class Admin extends Authenticatable
{
	use Notifiable;
 
    protected $table = "admin";

	protected $fillable =[
		'_token',
		'name',
        'short_descript',
        'email',
        'shw_email',
        'username',
        'address',
        'lat',
        'lng',
        'phone',
        'password',
        'shw_password',
        'phone_contact',
        'logo',
        'terms_title',
        'terms_descript',
        'terms',
        'about_title',
        'about_descript',
        'about',
        'privacy_title',
        'privacy_descript',
        'privacy',
        'fb',
        'insta',
        'twitter',
        'youtube'
	];

    public function rules($type)
    {
        if($type === 'add')
        {
            return [

            'username' => 'required|unique:admin',

            ];
        }
        else
        {
            return [

            'username'     => 'required|unique:admin,username,'.$type,

            ];
        }
    }
    
    public function validate($data,$type)
    {

        $validator = Validator::make($data,$this->rules($type));       
        if($validator->fails())
        {
            return $validator;
        }
    }
    /*
	|------------------------------------------------------------------
	|Checking for current admin password
	|@password = admin password
	|------------------------------------------------------------------
	*/
	public function matchPassword($password)
	{
	  if(auth()->guard('admin')->attempt(['username' => Auth()->guard('admin')->user()->username, 'password' => $password]))
	  {
		  return false;
	  }
	  else
	  {
		  return true;
	  }
	}

	/*
	|---------------------------------
	|Update Account Data
	|---------------------------------
	*/
	public function updateData($data)
	{
        $a                  	   = isset($data['lid']) ? array_combine($data['lid'], $data['l_store_type']) : [];
		$update 					= Admin::find(Auth::guard('admin')->user()->id);
		$update->name 				= isset($data['name']) ? $data['name'] : null;
		$update->email 				= isset($data['email']) ? $data['email'] : null;
		$update->username 			= isset($data['username']) ? $data['username'] : null;
		$update->fb 				= isset($data['fb']) ? $data['fb'] : null;
		$update->insta 				= isset($data['insta']) ? $data['insta'] : null;
		$update->twitter 			= isset($data['twitter']) ? $data['twitter'] : null;
		$update->youtube 			= isset($data['youtube']) ? $data['youtube'] : null;
		$update->currency 			= isset($data['currency']) ? $data['currency'] : null;
		$update->costs_ship 	    = isset($data['costs_ship']) ? $data['costs_ship'] : 0;
		$update->c_type 			= isset($data['c_type']) ? $data['c_type'] : 0;
		$update->c_value 			= isset($data['c_value']) ? $data['c_value'] : 0;
		$update->min_distance       = isset($data['min_distance']) ? $data['min_distance'] : 0;
		$update->max_distance_staff = isset($data['max_distance_staff']) ? $data['max_distance_staff'] : 0;
        $update->min_value          = isset($data['min_value']) ? $data['min_value'] : 0;
		$update->store_type 		= isset($data['store_type']) ? $data['store_type'] : null;
		$update->paypal_client_id 	= isset($data['paypal_client_id']) ? $data['paypal_client_id'] : null;
		$update->stripe_client_id 	= isset($data['stripe_client_id']) ? $data['stripe_client_id'] : null;
		$update->stripe_api_id 		= isset($data['stripe_api_id']) ? $data['stripe_api_id'] : null;
		$update->ApiKey_google   	= isset($data['ApiKey_google']) ? $data['ApiKey_google'] : null;
		$update->comm_stripe   	    = isset($data['comm_stripe']) ? $data['comm_stripe'] : null;
		$update->send_terminal      = isset($data['send_terminal']) ? $data['send_terminal'] : 0;
		$update->max_cash 			= isset($data['max_cash']) ? $data['max_cash'] : 0;
		$update->s_data 			= serialize($a);

		if(isset($data['new_password']))
		{
			$update->password = bcrypt($data['new_password']);
			$update->shw_password = $data['new_password'];
		}

		if(isset($data['logo']))
        {
            $filename   = time().rand(111,699).'.' .$data['logo']->getClientOriginalExtension(); 
            $data['logo']->move("upload/admin/", $filename);   
            $update->logo = $filename;   
        }

		$update->save();

	}

	public function getAll()
	{
		return Admin::where('id','!=',1)->get();
	}

	public function addNew($data,$type)
    {
        $add                    = $type === 'add' ? new Admin : Admin::find($type);
       	$add->username 			= isset($data['username']) ? $data['username'] : null;
       	$add->name 				= isset($data['name']) ? $data['name'] : null;
       	$add->perm 				= isset($data['perm']) ? implode(",", $data['perm']) : null;
		$add->city_id           = isset($data['city_id']) ? $data['city_id'] : 0;

        if(isset($data['password']))
        {
            $add->password      = bcrypt($data['password']);
            $add->shw_password  = $data['password'];
        }

        $add->save();
    }

	public function overview()
	{
		return [ 
			'users'  		=> AppUser::count(),
			'colonies'  	=> Colonies::count(),
			'mercados'		=> Mercaditos::count(),
			'lastPays'		=> $this->last_Pays(),
			'last_Pays_Commerce' => $this->last_Pays_Commerce(),
			'cobroHoy'      => OrdersMarket::whereDate('created_at','LIKE','%'.date('m-d').'%')->sum('costo'),
			'visiteM'      => OrdersMarket::whereDate('created_at','LIKE','%'.date('m-d').'%')->count()
		];
	}

	public function getMonthName($type)
	{
		 $month = date('m') - $type;
		 
		 return $type == 0 ? date('F') : date('F',strtotime(date('Y').'-'.$month));
	}

	public function getDayName($type)
	{
		$day = date('d') - $type;
		 
		return $type == 0 ? date('l') : date('l',strtotime(date('Y').'- '.$type.' day'));
	}

	public function chartUsersSign($type)
	{
		$month      = date('Y-m',strtotime(date('Y-m').' - '.$type.' month'));
		
		$online   = AppUser::where('status',0)->whereDate('created_at','LIKE',$month.'%')->count();
		$offline  = AppUser::where('status',1)->whereDate('created_at','LIKE',$month.'%')->count();

		return ['online' => $online,'offline' => $offline];
	}

	public function chartConnections($type)
	{
		$month      = date('Y-m',strtotime(date('Y-m').' - '.$type.' month'));
		
		$online   = 0; 

		return ['online' => $online];
	}

	public function getStoreData($data,$index,$type)
	{
		
		if(isset($data[$index]))
		{
			return $data[$index][$type];
		}
		else
		{
			return null;
		}
	}

	public function getSData($data,$id,$field)
    {
        $data = unserialize($data);

        return isset($data[$id]) ? $data[$id] : null;
    }

    public function hasPerm($perm)
	{
		$array = explode(",", Auth::guard('admin')->user()->perm);

		if(in_array($perm,$array) || in_array("All",$array))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function last_Pays()
	{

		$data = [];
		$orders = OrdersMarket::OrderBy('id','DESC')->limit(5)->get();

		foreach ($orders as $key) {

			$chkMerados = Mercaditos::find($key->market_id);

			if (isset($chkMerados->id) && $chkMerados->type == 0) {
				
				$data[] = [
					'id' => $key->id,
					'user' => AppUser::find($key->app_user_id)->name.' '.AppUser::find($key->app_user_id)->last_name,
					'colonie' => Colonies::find($key->colonie_id)->name, 
					'contribuyente' => $key->contribuyente,
					'giro' => $key->giro,
					'metros' => $key->metros,
					'costo' => $key->costo,
					'cuota' => $key->cuota,
					'extras' => $key->extras
				];
				
			}
		}

		return $data;
	}

	public function last_Pays_Commerce()
	{

		$data = [];
		$orders = OrdersMarket::OrderBy('id','DESC')->limit(5)->get();

		foreach ($orders as $key) {
			$chkMerados = Mercaditos::find($key->market_id);

			if (isset($chkMerados->id) && $chkMerados->type == 1) {
				$data[] = [
					'id' => $key->id,
					'user' => AppUser::find($key->app_user_id)->name.' '.AppUser::find($key->app_user_id)->last_name,
					'colonie' => Colonies::find($key->colonie_id)->name, 
					'contribuyente' => $key->contribuyente,
					'giro' => $key->giro,
					'metros' => $key->metros,
					'costo' => $key->costo,
					'cuota' => $key->cuota,
					'extras' => $key->extras
				];
			}
		}

		return $data;
	}

}
