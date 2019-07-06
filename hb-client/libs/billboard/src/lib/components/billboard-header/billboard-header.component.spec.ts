import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BillboardHeaderComponent } from './billboard-header.component';

describe('BillboardHeaderComponent', () => {
  let component: BillboardHeaderComponent;
  let fixture: ComponentFixture<BillboardHeaderComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BillboardHeaderComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BillboardHeaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
