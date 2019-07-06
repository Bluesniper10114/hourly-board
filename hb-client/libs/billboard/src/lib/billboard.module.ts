import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';

import {
  SharedFuseModule
} from '@hourly-board-workspace/shared/fuse';

import { BillboardMainComponent } from './components/billboard-main/billboard-main.component';

import { BillboardService } from './services/billboard.service';
import { BillboardFooterComponent } from './components/billboard-footer/billboard-footer.component';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { BillboardAddCommentComponent } from './components/billboard-add-comment/billboard-add-comment.component';
import { BillboardHeaderComponent } from './components/billboard-header/billboard-header.component';
import { BillboardHoursTableComponent } from './components/billboard-hours-table/billboard-hours-table.component';
import { BillboardAddDownTimeComponent } from './components/billboard-add-down-time/billboard-add-down-time.component';
import { MonitorsListComponent } from './components/monitors-list/monitors-list.component';
import { MonitorsListTableComponent } from './components/monitors-list-table/monitors-list-table.component';

const routes: Routes = [
  {
    path: '',
    redirectTo: 'monitor',
    pathMatch: 'full'
  },
  {
    path: 'monitor',
    component: BillboardMainComponent,
    resolve: {
      monitor: BillboardService
    }
  },
  {
    path: 'monitor/:id',
    component: BillboardMainComponent,
    resolve: {
      monitor: BillboardService
    }
  },
  {
    path: 'monitors',
    component: MonitorsListComponent
  }
];
@NgModule({
  imports: [
    CommonModule,
    SharedFuseModule,
    TranslateModule,
    HttpClientModule,
    RouterModule.forChild(routes)
  ],
  declarations: [
    BillboardMainComponent,
    BillboardFooterComponent,
    BillboardAddCommentComponent,
    BillboardHeaderComponent,
    BillboardHoursTableComponent,
    BillboardAddDownTimeComponent,
    MonitorsListComponent,
    MonitorsListTableComponent
  ],
  providers: [
    BillboardService
  ],
  entryComponents: [BillboardAddCommentComponent, BillboardAddDownTimeComponent]
})
export class BillboardModule {}
