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

  post_data:any = '';
  carTypes:any = '';

  carType:any;
  customerName:string;
  customerMobile:number;
  latitude:number;
  longitude:number;
  kmRadius:number;
  driverSelected:number;
  bookingId:string;
  endTripLatitude:number;
  endTripLongitude:number;
  endTripTime:number;
  bookingDetails:any;

  displayAssignButton:boolean = false;
  displayNearByCabs:boolean = false;
  displayCustomerDetailsContainer:boolean = true;
  displayStartTrip:boolean = false;
  endTripContainer:boolean = false;
  bookingDetailsContainer:boolean = false;

  nearByCabs:any;

  ngOnInit() {
		this.post_data = '{"type" : "GET_CAR_TYPES"}';
    this.apiService.postApiCall("http://localhost/temp/angualr_taxi/taxi/api/index.php?format=JSON", this.post_data)
      .subscribe(
    (data:any) => {
        if(data.status == "Success"){
          this.carTypes = data.car_types;
        }else{
          alert("Failed Loading Car Types");
        }
    });
  }

  getNearByCabs(){
    this.post_data = '{"type" : "GET_NEAR_BY_CABS", "latitude" : "'+this.latitude+'", "longitude" : "'+this.longitude+'", "car_type" : "'+this.carType+'","km_radius" : "'+this.kmRadius+'"}';
    this.apiService.postApiCall("http://localhost/temp/angualr_taxi/taxi/api/index.php?format=JSON", this.post_data)
      .subscribe(
    (data:any) => {
        console.log(data);
        if(data.status == "Success"){
          this.nearByCabs = data.cabs;
          this.displayNearByCabs = true;
          this.displayAssignButton = true;
        }else{
          alert("Failed Loading Nearby Cars");
        }
    });
  }

  assignCab(){
    this.post_data = '{"type" : "ASSIGN_CAB", "customer_name" : "'+this.customerName+'", "customer_number" : "'+this.customerMobile+'", "customer_latitude" : "'+this.latitude+'", "customer_longitude" : "'+this.longitude+'", "driver_id" : "'+this.driverSelected+'"}';
    this.apiService.postApiCall("http://localhost/temp/angualr_taxi/taxi/api/index.php?format=JSON", this.post_data)
      .subscribe(
    (data:any) => {
        console.log(data);
        if(data.status == "Success"){
          this.bookingId = data.booking_id;
          this.displayNearByCabs = true;
          this.displayStartTrip = true;
          this.displayAssignButton = false;
        }else{
          alert("Failed to assign Cab");
        }
    });
  }

  startTrip(){
    this.post_data = '{"type" : "START_TRIP", "booking_id" : "'+this.bookingId+'"}';
    this.apiService.postApiCall("http://localhost/temp/angualr_taxi/taxi/api/index.php?format=JSON", this.post_data)
      .subscribe(
    (data:any) => {
        console.log(data);
        if(data.status == "Success"){
          this.displayCustomerDetailsContainer = false;
          this.endTripContainer = true;
        }else{
          alert("Failed to Start Trip");
        }
    });
  }

  endTrip(){
    this.post_data = '{"type" : "END_TRIP", "booking_id" : "'+this.bookingId+'", "customer_latitude" : "'+this.endTripLatitude+'", "customer_longitude" : "'+this.endTripLongitude+'", "trip_time" : "'+this.endTripTime+'"}';
    this.apiService.postApiCall("http://localhost/temp/angualr_taxi/taxi/api/index.php?format=JSON", this.post_data)
      .subscribe(
    (data:any) => {
        console.log(data);
        if(data.status == "Success"){
          this.endTripContainer = false;
          this.bookingDetails = "Total Distance Travelled : "+data.Total_distance_travelled+" | Time Price : "+data.time_price+" | KiloMeter Price : "+data.km_price+" | Total Price : "+data.total_price;
          this.bookingDetailsContainer = true;
        }else{
          alert("Failed to End Trip");
        }
    });
  }

}
