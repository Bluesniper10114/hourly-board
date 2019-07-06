import { Component } from '@angular/core';
import { MatDialogRef } from '@angular/material';

@Component({
  // tslint:disable-next-line:component-selector
  selector: 'fuse-confirm-dialog',
  templateUrl: './confirm-dialog.component.html',
  styleUrls: ['./confirm-dialog.component.scss']
})
export class FuseConfirmDialogComponent {
  public confirmMessage: string;

  /**
   * Constructor
   *
   * @param {MatDialogRef<FuseConfirmDialogComponent>} dialogRef
   */
  constructor(public dialogRef: MatDialogRef<FuseConfirmDialogComponent>) {}
}
