import { Component, OnInit } from '@angular/core';
import { ApiService } from './api.service'
import {Router} from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
	
	isLoggedIn:string = '';

	constructor(private apiService:ApiService, private router: Router){

	}

	ngOnInit() {
		this.isLoggedIn = localStorage.getItem('current_user');
		if(this.isLoggedIn == null){
			console.log("user not logged in");
			this.router.navigateByUrl('/login');
		}
  	}
}
