import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import {
  MatButtonModule,
  MatIconModule,
  MatMenuModule,
  MatToolbarModule
} from '@angular/material';

import * as fromSharedFuse from '@hourly-board-workspace/shared/fuse';

import { ToolbarComponent } from './toolbar.component';

@NgModule({
  declarations: [ToolbarComponent],
  imports: [
    RouterModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatToolbarModule,
    fromSharedFuse.SharedFuseModule,
    fromSharedFuse.FuseSearchBarModule,
    fromSharedFuse.FuseShortcutsModule
  ],
  exports: [ToolbarComponent]
})
export class ToolbarModule {}
