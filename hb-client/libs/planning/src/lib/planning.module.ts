import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { NgAggregatePipesModule } from 'angular-pipes';

import {
  SharedFuseModule,
  AuthGuard,
  HttpHeaderInterceptorService,
  ErrorInterceptorService
} from '@hourly-board-workspace/shared/fuse';

import { PlanningOverviewComponent } from './components/planning-overview/planning-overview.component';
import { PlanningService } from './services/planning.service';
import { LineDateSearchHeaderComponent } from './components/line-date-search-header/line-date-search-header.component';
import { DataSetsTableComponent } from './components/data-sets-table/data-sets-table.component';

const routes: Routes = [
  {
    path: '',
    redirectTo: 'planning-overview',
    pathMatch: 'full'
  },
  {
    path: 'login',
    loadChildren: '@hourly-board-workspace/login#LoginModule'
  },
  {
    path: 'planning-overview',
    canActivate: [AuthGuard],
    component: PlanningOverviewComponent
  }
];

@NgModule({
  imports: [
    CommonModule,
    SharedFuseModule,
    TranslateModule,
    HttpClientModule,
    NgAggregatePipesModule,
    RouterModule.forChild(routes)
  ],
  declarations: [
    PlanningOverviewComponent,
    LineDateSearchHeaderComponent,
    DataSetsTableComponent
  ],
  providers: [
    PlanningService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HttpHeaderInterceptorService,
      multi: true
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: ErrorInterceptorService,
      multi: true
    }
  ]
})
export class PlanningModule {}
