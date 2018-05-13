import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';

@Injectable()
export class ApiService {

  constructor(private httpClient:HttpClient) { }

  loginApiCall(username, password, url, data){
  	return this.httpClient.post(url,
      data,
		{
		   headers: new HttpHeaders().set('Authorization', "Basic " + btoa(username + ":" + password))
		}
  	);
  }

  getApiCall(url){
    return this.httpClient.get(url,
    {
       headers: new HttpHeaders().set('Authorization', "Basic " + localStorage.getItem('current_user_token'))
    }
    );
  }

  postApiCall(url, data){
  	return this.httpClient.post(url, 
  		data,
		{
		   headers: new HttpHeaders().set('Authorization', "Basic " + localStorage.getItem('current_user_token')).set('Content-Type', "application/json")
		}
  	);
  }

  putApiCall(url, data){
    return this.httpClient.put(url, 
      data,
    {
       headers: new HttpHeaders().set('Authorization', "Basic " + localStorage.getItem('current_user_token')).set('Content-Type', "application/json")
    }
    );
  }
}
