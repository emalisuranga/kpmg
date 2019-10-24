import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReductionStatedCapitalComponent } from './reduction-stated-capital.component';

describe('ReductionStatedCapitalComponent', () => {
  let component: ReductionStatedCapitalComponent;
  let fixture: ComponentFixture<ReductionStatedCapitalComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReductionStatedCapitalComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReductionStatedCapitalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
