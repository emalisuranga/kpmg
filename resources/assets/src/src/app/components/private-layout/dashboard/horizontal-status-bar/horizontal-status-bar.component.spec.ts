import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { HorizontalStatusBarComponent } from './horizontal-status-bar.component';

describe('HorizontalStatusBarComponent', () => {
  let component: HorizontalStatusBarComponent;
  let fixture: ComponentFixture<HorizontalStatusBarComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ HorizontalStatusBarComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(HorizontalStatusBarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
