import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MemoDateResubmitComponent } from './memo-date-resubmit.component';

describe('MemoDateResubmitComponent', () => {
  let component: MemoDateResubmitComponent;
  let fixture: ComponentFixture<MemoDateResubmitComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MemoDateResubmitComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MemoDateResubmitComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
