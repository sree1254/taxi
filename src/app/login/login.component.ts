import { Component, OnInit } from '@angular/core';
import { ApiService } from '../api.service'
import {Router} from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  constructor(private apiService:ApiService, private router: Router){ }

  	username:string = '';
	password:string = '';
	post_data:any = '';
	isLoggedIn:string = '';

  ngOnInit(){
		this.isLoggedIn = localStorage.getItem('current_user');
		if(this.isLoggedIn != null){
			console.log("user already logged in");
			//alert(this.activatedRoute.routeConfig);
			this.router.navigateByUrl('/dashboard');
		}
	}

  check_login(){
		this.post_data = '{"type" : "LOGIN"}';
      	this.apiService.loginApiCall(this.username, this.password, "http://localhost/temp/angualr_taxi/taxi/api/index.php?format=JSON", this.post_data)
	  	.subscribe(
		(data:any) => {
			console.log(data);
			if(data.status == "Success"){
				localStorage.setItem('current_user', this.username);
				localStorage.setItem('current_user_token', btoa(this.username + ":"+this.password));
				this.router.navigateByUrl('/dashboard');
			}else{
				alert("Invalid Credentials");
			}
		});
	}

}
