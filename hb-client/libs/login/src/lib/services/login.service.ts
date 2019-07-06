import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { Md5 } from 'ts-md5/dist/md5';

import { JwtHelperService } from '@auth0/angular-jwt';
import { environment } from '@env/environment';

import { LoginModel } from '../models/login.model';
import {
  CustomResponse,
  NotifyService,
  CommonHandlersService
} from '@hourly-board-workspace/shared/fuse';

@Injectable({
  providedIn: 'root'
})
export class LoginService {
  baseUrl = environment.devApi;

  onError: BehaviorSubject<string> = new BehaviorSubject('');

  constructor(
    private http: HttpClient,
    private commonHandler: CommonHandlersService
  ) {
  }

  login(user: LoginModel): Observable<any> {
    const hashedUser = {
      username: user.username,
      password: Md5.hashStr(user.password)
    };

    // debugger;
    return this.http
      .post(`${this.baseUrl}/login`, hashedUser)
      .pipe(
        map((response: CustomResponse) => {
          debugger;
          this.commonHandler.handleLoader(false);
          return this.commonHandler.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;
          this.onError.next(error);
          return throwError(error);
        })
      );
  }


}
