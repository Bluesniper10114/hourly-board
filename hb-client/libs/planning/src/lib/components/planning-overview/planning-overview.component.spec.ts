import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PlanningOverviewComponent } from './planning-overview.component';

describe('PlanningOverviewComponent', () => {
  let component: PlanningOverviewComponent;
  let fixture: ComponentFixture<PlanningOverviewComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PlanningOverviewComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PlanningOverviewComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
