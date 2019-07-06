import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '@hourly-board-workspace/shared/fuse';

const routes: Routes = [
  {
    path: '',
    pathMatch: 'full',
    redirectTo: 'billboard'
  },
  {
    path: 'billboard',
    loadChildren: '@hourly-board-workspace/billboard#BillboardModule'
  },
  {
    path: 'management',
    loadChildren: '@hourly-board-workspace/planning#PlanningModule'
  },
  {
    path: '**',
    redirectTo: 'billboard'
  }
];

@NgModule({
  imports: [
    CommonModule,
    RouterModule.forRoot(routes, { initialNavigation: 'enabled' })
  ],
  declarations: [],
  exports: [RouterModule]
})
export class AppRoutingModule {}
