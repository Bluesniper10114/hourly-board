import { Injectable } from '@angular/core';
import { environment } from '@env/environment';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import {
  NotifyService,
  FooterModel,
  CustomResponse,
  CommonHandlersService
} from '@hourly-board-workspace/shared/fuse';

@Injectable({
  providedIn: 'root'
})
export class FooterService {
  apiUrl = environment.devApi;

  constructor(private http: HttpClient,
    private commonHandler:CommonHandlersService) {

  }

  /**
   * @description Get Board Footer
   */
  getBoardFooter(): Observable<FooterModel> {
    // debugger;
    if (!this.commonHandler.loading) {
      this.commonHandler.loading = true;
      this.commonHandler.loading$.next(this.commonHandler.loading);
    }
    return this.http.get<CustomResponse>(`${this.apiUrl}/board/footer`).pipe(
      map((response: CustomResponse) => {
        this.commonHandler.loading = false;
        this.commonHandler.loading$.next(this.commonHandler.loading);
        return this.commonHandler.handleResponse(response);
      }),
      catchError((error: any) => {
        debugger;
        return throwError(error);
      })
    );
  }

}
