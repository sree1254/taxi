import { Component, OnInit } from '@angular/core';
import { ApiService } from '../api.service'
import {Router} from '@angular/router';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {

  constructor(private apiService:ApiService, private router: Router){

  }

  isLoggedIn:string = '';
  showDashboardContainer:boolean = false;

  ngOnInit() {
		this.isLoggedIn = localStorage.getItem('current_user');
		if(this.isLoggedIn != null){
			this.showDashboardContainer = true;
		}
  	}

}
