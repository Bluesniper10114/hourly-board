import { Injectable } from '@angular/core';
import { JwtHelperService } from '@auth0/angular-jwt';
import { environment } from '@env/environment';
import { HttpClient } from '@angular/common/http';
import { CommonHandlersService } from './common-handlers.service';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  helper;
  baseUrl = environment.devApi;
  user: {
    username: string;
    token: string;
  };
  constructor(
    private http: HttpClient,
    private commonHandler: CommonHandlersService
  ) {
    this.helper = new JwtHelperService();
  }

  getToken(): string {
    // debugger;
    const userSession = JSON.parse(localStorage.getItem('UserSession')) as any;
    return userSession ? userSession.token : null;
  }

  getUserName():string {
    let userSession = JSON.parse(localStorage.getItem('UserSession')) as any;
    if(this.user) {
      userSession = this.user;
    }
    return userSession? userSession.username : null;
  }

  setToken(response: any, username: string): void {
    this.user = {
      token:response.token,
      username
    }
    localStorage.setItem('UserSession', JSON.stringify(this.user));
  }

  public isAuthenticated(): boolean {
    // debugger;
    const token = this.getToken();
    if (token) {
      // return !this.helper.isTokenExpired(token);
      return true;
    } else {
      return false;
    }
  }

  logout(): Observable<any> {
    debugger;
    return this.http.post(`${this.baseUrl}/logout`, {username:this.getUserName()}).pipe(
      map((response: any) => {
        this.user =null;
        localStorage.removeItem('UserSession');
      }),
      catchError((error: any) => {
        debugger;
        return throwError(error);
      })
    );
  }
}
