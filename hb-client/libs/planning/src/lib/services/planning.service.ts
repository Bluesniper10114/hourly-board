import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';

import { environment } from '@env/environment';
import {
  CommonHandlersService,
  CustomResponse,
  DrpDownListModel
} from '@hourly-board-workspace/shared/fuse';
import { Observable, throwError, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { DataSetsModel } from '../models/datasets.model';


@Injectable({
  providedIn: 'root'
})
export class PlanningService {
  apiUrl = environment.devApi;

  constructor(
    private http: HttpClient,
    private router: Router,
    private commonHandlers: CommonHandlersService
  ) {}

  /**
   * @Description get Planning Lines List to populate drop down
   */
  getPlanningLines(): Observable<DrpDownListModel[]> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http.get(`${this.apiUrl}/planning/lines`).pipe(
      map((response: CustomResponse) => {
        // debugger;
        this.commonHandlers.handleLoader(false);
        return this.commonHandlers.handleResponse(response);
      }),
      catchError((error: any) => {
        debugger;
        return throwError(error);
      })
    );

    // return of(planningOverview.lines);
  }

  /**
   * @Description get Planning Dates List to populate drop down
   */
  getPlanningDates(): Observable<string[]> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }
    return this.http.get(`${this.apiUrl}/planning/dates`).pipe(
      map((response: CustomResponse) => {
        // debugger;
        this.commonHandlers.handleLoader(false);
        return this.commonHandlers.handleResponse(response);
      }),
      catchError((error: any) => {
        debugger;
        return throwError(error);
      })
    );

    // return of(planningOverview.dates);
  }

  /**
   * @Description Get planning datasets to view on table
   * @params Lines , Dates , shifts
   */
  getPlanningDataSets(
    lines: number[],
    dates: string[],
    shifts: string[]
  ): Observable<DataSetsModel> {
    if (!this.commonHandlers.loading) {
      this.commonHandlers.handleLoader(true);
    }

    return this.http
      .post(`${this.apiUrl}/planning/datasets`, { lines, dates, shifts })
      .pipe(
        map((response: CustomResponse) => {
          // debugger;
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
