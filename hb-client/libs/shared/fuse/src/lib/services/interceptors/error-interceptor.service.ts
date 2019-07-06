import { Injectable } from '@angular/core';

import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpErrorResponse,
  HttpResponse
} from '@angular/common/http';
import { Observable, throwError } from 'rxjs';

import 'rxjs/add/operator/do';
import 'rxjs/add/operator/catch';
import 'rxjs/add/observable/throw';
import 'rxjs/add/observable/of';
import { NotifyService } from '../notify.service';

@Injectable({
  providedIn: 'root'
})
export class ErrorInterceptorService {
  constructor(private notify: NotifyService) {}

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<any> {
    return next.handle(request).catch((err: HttpErrorResponse) => {
      // debugger;
      if (err.status === 404) {
        this.notify.showError(
          err.status.toString(),
          err.message ? err.message : err.error
        );
      }

      if (err.status === 500) {
        this.notify.showError(
          err.status.toString(),
          err.message ? err.message : err.error
        );
      }

      if (err.status === 400) {
        this.notify.showError(
          err.status.toString(),
          err.message ? err.message : err.error
        );
      }

      return throwError(err);
    });
  }
}
