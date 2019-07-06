import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BillboardAddDownTimeComponent } from './billboard-add-down-time.component';

describe('BillboardAddDownTimeComponent', () => {
  let component: BillboardAddDownTimeComponent;
  let fixture: ComponentFixture<BillboardAddDownTimeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BillboardAddDownTimeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BillboardAddDownTimeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
