import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { CustomResponse } from '../models';
import { NotifyService } from './notify.service';

@Injectable({
  providedIn: 'root'
})
export class CommonHandlersService {
  loading: boolean;

  loading$: BehaviorSubject<boolean>;
  constructor(private notify: NotifyService) {
    this.loading$ = new BehaviorSubject(false);
  }

  handleResponse(response: CustomResponse): any {
    // debugger;
    if (response.success) {
      return response.content;
    } else {
      if (response.errors.length) {
        this.logErrors(response.errors);
      }
    }
  }

  handleLoader(statusFlag: boolean): void {
    this.loading = statusFlag;
    this.loading$.next(this.loading);
  }

  logErrors(errors: { message?: string; error?: string }[]): void {
    errors.forEach(error => {
      this.notify.showError(
        'Error',
        error.message ? error.message : error.error
      );
    });
  }
}
