import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {
  Observable,
  throwError,
  BehaviorSubject,
  of,
  Subscription
} from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import {
  ActivatedRouteSnapshot,
  Resolve,
  RouterStateSnapshot,
  Router
} from '@angular/router';

import { environment } from '@env/environment';
import { BillboardModel } from '../models/billboard.model';

import {
  NotifyService,
  CustomResponse,
  FooterModel,
  CommonHandlersService,
  DrpDownListModel
} from '@hourly-board-workspace/shared/fuse';
import { MonitorModel } from '../models/monitor.model';
import { DownTimeModel, DownTimeSaveDTO } from '../models/downTime.model';

@Injectable({
  providedIn: 'root'
})
export class BillboardService implements Resolve<any> {
  apiUrl = environment.devApi;

  billboard: BillboardModel;
  monitorId: number;

  onBillboardChanged: BehaviorSubject<any>;
  onMonitorIdChanged: BehaviorSubject<any>;

  onGetDownTimeReasons: BehaviorSubject<DrpDownListModel[]>;

  constructor(
    private http: HttpClient,
    private router: Router,
    private commonHandlers: CommonHandlersService
  ) {
    // Set defaults
    this.onBillboardChanged = new BehaviorSubject({});
    this.onMonitorIdChanged = new BehaviorSubject(null);
    this.onGetDownTimeReasons = new BehaviorSubject([]);
  }

  resolve(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<any> | Promise<any> | any {
    return new Promise((resolve, reject) => {
      // debugger;
      const id = +route.params.id;
      if (id) {
        // debugger;
        this.monitorId = id;
        this.onMonitorIdChanged.next(this.monitorId);
        resolve();
      } else {
        this.getMonitorId().subscribe(
          (response: CustomResponse) => {
            debugger;
            if (response.success) {
              this.monitorId = response.content.monitor.id;
              this.onMonitorIdChanged.next(this.monitorId);
            } else {
              this.router.navigate(['/billboard/monitors']);
            }
            resolve();
          },
          response => {
            reject();
          }
        );
      }
    });
  }

  /**
   * @Description getMonitors List
   */
  getMonitors(): Observable<MonitorModel[]> {
    // debugger;
    return this.http.get(`${this.apiUrl}/monitors/all`).pipe(
      map((response: CustomResponse) => {
        // debugger;
        return this.commonHandlers.handleResponse(response);
      }),
      catchError((error: any) => {
        debugger;
        return throwError(error);
      })
    );
  }

  /**
   * @description Get Monitor ID To Use in Get Billboard Data
   * @param ip
   * @returns {Observable<any>}
   */
  getMonitorId(): Observable<any> {
    return this.http.get(`${this.apiUrl}/monitors/monitorId`).pipe(
      catchError((error: any) => {
        debugger;
        this.router.navigate(['/billboard/monitors']);
        return throwError(error);
      })
    );
  }

  /**
   * @description Get Board Footer
   */
  getBoardFooter(): Observable<FooterModel> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http.get<CustomResponse>(`${this.apiUrl}/board/footer`).pipe(
      map((response: CustomResponse) => {
        this.commonHandlers.handleLoader(false);
        return this.commonHandlers.handleResponse(response);
      }),
      catchError((error: any) => {
        debugger;
        return throwError(error);
      })
    );
  }

  /**
   *@description Get Billboard Data by monitor ID
   *@param id
   *@returns {Observable<BillboardModel>}
   */
  getBillboard(): Observable<BillboardModel> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .get<CustomResponse>(`${this.apiUrl}/shopfloor/${this.monitorId}`)
      .pipe(
        map((response: CustomResponse) => {
          // debugger;
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          // debugger;
          return throwError(error);
        })
      );
  }

  /**
   * @description Get Comments to fill the multi selectbox in the billboard data table
   */
  getComments(): Observable<DrpDownListModel[]> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .get<CustomResponse>(`${this.apiUrl}/shopfloor/comments`)
      .pipe(
        map((response: CustomResponse) => {
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          // debugger;

          return throwError(error);
        })
      );
  }

  /**
   * @description Post Comment
   * @param payload { hourlyId: number;    comments: number[]; }
   */
  postComments(payload: {
    hourlyId: number;
    comments: number[];
  }): Observable<any> {
    // debugger;
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    console.log(payload);
    return this.http
      .post(`${this.apiUrl}/shopfloor/comments/save`, payload)
      .pipe(
        map((response: CustomResponse) => {
          debugger;
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;
          return throwError(error);
        })
      );
  }

  /**
   * @description Get escalations to fill the multi selectbox in the billboard data table
   */
  getEscalations(): Observable<DrpDownListModel[]> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .get<CustomResponse>(`${this.apiUrl}/shopfloor/escalations`)
      .pipe(
        map((response: CustomResponse) => {
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          // debugger;

          return throwError(error);
        })
      );
  }

  /**
   * @description Post Escalations
   * @param payload { hourlyId: number;    comments: number[]; }
   */
  postEscalations(payload: {
    hourlyId: number;
    escalations: number[];
  }): Observable<any> {
    // debugger;
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    console.log(payload);
    return this.http
      .post(`${this.apiUrl}/shopfloor/escalations/save`, payload)
      .pipe(
        map((response: CustomResponse) => {
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;

          return throwError(error);
        })
      );
  }

  /**
   * @description Close an Hour Row
   * @param payload { hourlyId: number;    operatorBarcode: string;}
   */
  signOffHour(payload: {
    hourlyId: number;
    operatorBarcode: string;
  }): Observable<any> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http.post(`${this.apiUrl}/shopfloor/signoffhour`, payload).pipe(
      map((response: CustomResponse) => {
        debugger;
        this.commonHandlers.handleLoader(false);
        return this.commonHandlers.handleResponse(response);
      }),
      catchError((error: any) => {
        debugger;

        return throwError(error);
      })
    );
  }

  /**
   * @description Close the Whole shift
   * @param payload {    shiftLogSignOffId: number;    operatorBarcode: string;  }
   */
  signOffShift(payload: {
    shiftLogSignOffId: number;
    operatorBarcode: string;
  }): Observable<any> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .post(`${this.apiUrl}/shopfloor/signoffshift`, payload)
      .pipe(
        map((response: CustomResponse) => {
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;
          return throwError(error);
        })
      );
  }

  /**
   * @description Ge Down Time for specefic hour interval
   * @param hourly ID : number
   */

  getHourlyDownTime(hourlyId: number): Observable<DownTimeModel> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .get<CustomResponse>(`${this.apiUrl}/downtime/${hourlyId}`)
      .pipe(
        map((response: CustomResponse) => {
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;
          return throwError(error);
        })
      );
  }

  /**
   * @description Get the down time reasons
   */
  getDownTimeReasons(): Subscription {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .get<CustomResponse>(`${this.apiUrl}/downtime/reasons`)
      .pipe(
        map((response: CustomResponse) => {
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;
          return throwError(error);
        })
      )
      .subscribe(data => {
        this.onGetDownTimeReasons.next(data);
      });
  }

  saveDownTime(payload: DownTimeSaveDTO): Observable<any> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http
      .post<CustomResponse>(`${this.apiUrl}/downtime/save`, payload)
      .pipe(
        map((response: CustomResponse) => {
          debugger;
          this.commonHandlers.handleLoader(false);
          return this.commonHandlers.handleResponse(response);
        }),
        catchError((error: any) => {
          debugger;
          return throwError(error);
        })
      );
  }
}
